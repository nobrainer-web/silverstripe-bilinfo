<?php


namespace NobrainerWeb\Bilinfo\Tasks;


use NobrainerWeb\Bilinfo\API\DataMapper;
use NobrainerWeb\Bilinfo\API\ListingsClient;
use NobrainerWeb\Bilinfo\Listings\Equipment;
use NobrainerWeb\Bilinfo\Listings\Listing;
use NobrainerWeb\Bilinfo\Listings\ListingImage;
use NobrainerWeb\Bilinfo\Listings\Make;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\SS_List;

class GetApiDataTask extends BuildTask
{
    protected $title = 'Bilinfo - Get data from the API';

    /**
     * List of errors (strings) that might have occured
     *
     * @var array
     */
    protected $errors = [];

    public function run($request)
    {
        $client = ListingsClient::create();
        $data = [];
        try {
            $data = $client->get();
        } catch (\Exception $e) {
            $this->log('ERROR ON DATA FETCH: ' . $e->getMessage());
            $this->extend('onFailedDataFetch', $e);

            return;
        }

        if (empty($data)) {
            $this->log('NO DATA');

            return;
        }

        $this->cleanUp();

        $written = $this->writeListings($data);

        $this->reportErrors();
    }

    protected function reportErrors()
    {
        if (empty($this->errors)) {
            return;
        }

        foreach ($this->errors as $err) {
            $this->log($err);

            //TODO some hook which can receive errors
        }
    }

    /**
     * cleanup existing data as it will be replaced ( has no external id so we cannot simply update it )
     */
    protected function cleanUp()
    {
        Make::get()->removeAll();
        ListingImage::get()->removeAll();
        Equipment::get()->removeAll();
    }

    /**
     * @param $data
     * @return SS_List
     */
    protected function writeListings($data): SS_List
    {
        $mapper = DataMapper::create($data);
        $listings = $mapper->mapListings();
        $written = ArrayList::create();
        if (!$listings->exists()) {
            return $written;
        }

        $dealers = $this->writeItems($mapper->mapDealers());
        $images = $this->writeItems($mapper->mapListingImages());
        $makes = $this->writeItems($mapper->mapMakes());
        $equipment = $this->writeItems($mapper->mapEquipment());

        foreach ($listings as $listing) {
            // all regular fields have been mapped to the object
            // now find any relations
            if (($dealerId = $listing->DealerId) && ($dealer = $dealers->find('ExternalID', $dealerId))) {
                unset($listing->DealerId);
                $listing->DealerID = $dealer->ID;
            }
            if (($make = $listing->Make) && ($makeItem = $makes->find('Title', $make))) {
                unset($listing->Make);
                $listing->MakeID = $makeItem->ID;
            }
            if (($equipmentList = $listing->EquipmentList) && !empty($equipmentList)) {
                $listing = $this->bindListingEquipment($listing, $equipment, $equipmentList);
            }
            if (($pictures = $listing->Pictures) && !empty($pictures)) {
                $listing = $this->bindListingImages($listing, $images, $pictures);
            }

            $written->push($this->writeItem($listing));
        }

        $this->log($written->dataClass() . ' wrote: ' . $written->count());

        return $written;
    }

    /**
     * @param Listing $listing
     * @param SS_List $images
     * @param array   $original
     * @return Listing
     */
    protected function bindListingImages(Listing $listing, SS_List $images, array $original): Listing
    {
        $filtered = $images->filterAny('URL', $original);

        if (!$filtered->exists()) {
            return $listing;
        }

        foreach ($filtered as $image) {
            $listing->ListingImages()->add($image);
        }

        return $listing;
    }

    /**
     * @param Listing $listing
     * @param SS_List $equipment
     * @param string  $original
     * @return Listing
     */
    protected function bindListingEquipment(Listing $listing, SS_List $equipment, array $original): Listing
    {
        $filtered = $equipment->filterAny('Title', $original);

        if (!$filtered->exists()) {
            return $listing;
        }

        foreach ($filtered as $equipment) {
            $listing->Equipment()->add($equipment);
        }

        return $listing;
    }

    /**
     * Write list of items to DB,
     *
     * @param SS_List $list
     * @return SS_List
     */
    protected function writeItems(SS_List $list): SS_List
    {
        $written = ArrayList::create();
        if (!$list->exists()) {
            return $written;
        }

        foreach ($list as $item) {
            $written->push($this->writeItem($item));
        }

        $this->log($written->dataClass() . ' wrote: ' . $written->count());

        return $written;
    }

    /**
     * @param $item
     * @return DataObject
     */
    protected function writeItem($item): DataObject
    {
        $externalID = $item->ExternalID ?? null;
        // attempt to find existing item
        if ($externalID) {
            $existing = DataObject::get_one($item->ClassName, ['ExternalID' => $externalID]);
            if ($existing) {
                return $this->updateItem($existing, $item->toMap());
            }
        }

        try {
            $item->write();
            $this->log('Wrote item ' . $item->ClassName . ' ' . $item->getTitle());
        } catch (\Exception $e) {
            $error = 'Error with' . $item->ClassName . ' , exception message: ' . $e->getMessage() . '. Data: ' . json_encode($item->toMap());
            $this->errors[] = $error;
        }

        return $item;
    }

    protected function updateItem($existingItem, array $data): DataObject
    {
        $existingItem->update($data);
        try {
            $existingItem->write();
            $this->log('Updated item ' . $existingItem->ClassName . ' ' . $existingItem->getTitle());
        } catch (\Exception $e) {
            $error = 'Error with' . $item->ClassName . '(ID ' . $item->ID . ') , exception message: ' . $e->getMessage();
            $this->errors[] = $error;
        }

        return $existingItem;
    }

    protected function log($msg)
    {
        if (!Director::is_cli()) {
            echo $msg;
            echo '<br>';
        }
    }
}