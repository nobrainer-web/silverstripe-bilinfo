<?php


namespace NobrainerWeb\Bilinfo\Tasks;


use NobrainerWeb\Bilinfo\API\ListingsClient;
use NobrainerWeb\Bilinfo\Listings\Listing;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\SS_List;

class CheckDeletedListingsTask extends BuildTask
{
    protected $title = 'Bilinfo - Check deleted listings';
    protected $description = 'Check if any of the listings currently in the DB are missing from the API response, then mark these as "Sold" (deleted)';
    private static $segment = 'bilinfo-deleted-listings-task';

    protected $verbose = false;

    public function run($request)
    {
        $data = $this->fetchData();
        $this->verbose = (bool)$request->getVar('verbose');
        if (empty($data)) {
            $this->log('NO DATA');

            return;
        }

        $listings = $this->findMissingListings($data);

        $marked = $this->markDeletedListings($listings);

        $this->log($marked->count() . ' listings were marked as deleted (sold)');

        $this->extend('onFinishedRun', $marked, $listings, $data);
    }

    /**
     * Calls the API to get the listings data
     *
     * @return array
     */
    public function fetchData(): array
    {
        $client = ListingsClient::create();
        $data = [];
        try {
            $data = $client->get();
        } catch (\Exception $e) {
            $this->log('ERROR ON DATA FETCH: ' . $e->getMessage());
            $this->extend('onFailedDataFetch', $e);

            return $data;
        }

        $this->extend('onAfterFetchData', $data);

        return $data;
    }

    /**
     * Find any Listings in the DB which have an ExternalID, that is NOT present in the API response (they are deleted)
     *
     * @param array $data
     * @return SS_List
     */
    public function findMissingListings(array $data): SS_List
    {
        $list = ArrayList::create();

        if (empty($data)) {
            return $list;
        }

        $idValues = [];
        foreach ($data as $listingData) {
            $idValues[] = (int)$listingData['Id'];
        }

        $listings = Listing::get()->excludeAny('ExternalID', $idValues)->exclude(['ExternalDeletedDate:not' => null]);

        return $listings;
    }

    /**
     * Mark these listings as deleted. This is done by setting the ExternalDeletedDate field
     *
     * @param SS_List $listings
     * @return SS_List
     */
    public function markDeletedListings(SS_List $listings): SS_List
    {
        $list = ArrayList::create();
        if (!$listings->exists()) {
            return $list;
        }

        $now = DBDatetime::now()->Format(DBDatetime::singleton()->getISOFormat());
        foreach ($listings as $listing) {
            $listing
                ->update(['ExternalDeletedDate' => $now])
                ->write();
            $list->push($listing);
        }

        return $list;
    }

    protected function log($msg)
    {
        if ($this->verbose) {
            Debug::message($msg, false);
        }
    }
}