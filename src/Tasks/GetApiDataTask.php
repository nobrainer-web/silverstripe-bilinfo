<?php


namespace NobrainerWeb\Bilinfo\Tasks;


use NobrainerWeb\Bilinfo\API\DataMapper;
use NobrainerWeb\Bilinfo\API\ListingsClient;
use NobrainerWeb\Bilinfo\Listings\Dealer;
use NobrainerWeb\Bilinfo\Listings\Equipment;
use NobrainerWeb\Bilinfo\Interfaces\Listing;
use NobrainerWeb\Bilinfo\Listings\Listing as ListingDataObject;
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

    /**
     * Specify for each model which field is checked, for an already existing item
     * For example on Listings, ExternalID field is used to determine if we write a new record, or simply update the existing one with corresponding ExternalID
     * On other models such as Make, it would simply be the Title field that is used to check, if a Make with that title already exists
     *
     * (if API items all had an ID that would of course be much better)
     *
     * @config array
     */
    private static $unique_field_identifiers = [
        ListingDataObject::class => 'ExternalID',
        Dealer::class            => 'ExternalID',
        Make::class              => 'Title',
        ListingImage::class      => 'URL',
        Equipment::class         => 'Title'
    ];

    public function run($request)
    {
        $data = $this->fetchData();

        if (empty($data)) {
            $this->log('NO DATA');

            return;
        }

        $this->cleanUp();

        $written = $this->writeListings($data);

        $this->reportErrors();

        $this->extend('onFinishedRun', $written, $data);
    }

    /**
     * @return array
     */
    protected function fetchData(): array
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

    protected function reportErrors()
    {
        if (empty($this->errors)) {
            return;
        }

        $this->extend('onErrorReport', $this->errors);

        foreach ($this->errors as $err) {
            $this->log($err);
        }
    }

    /**
     * cleanup existing data as it will be replaced ( has no external id so we cannot simply update it )
     */
    protected function cleanUp()
    {
        // You can use this to clean something before writing new data to the DB
    }

    /**
     * @param $data
     * @return SS_List
     */
    public function writeListings(array $data): SS_List
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

        $this->extend('onBeforeWriteListings', $listings);

        foreach ($listings as $listing) {
            // all regular fields have been mapped to the object
            // now find any relations
            $this->bindListingDealer($listing, $dealers);
            $this->bindListingMake($listing, $makes);

            $equipmentList = $listing->EquipmentList;
            $pictures = $listing->Pictures;

            // might update existing items, so adding relational objects should happen to the return element
            $writtenItem = $this->writeItem($listing);

            if (!empty($equipmentList)) {
                $writtenItem = $this->bindListingEquipment($writtenItem, $equipment, $equipmentList);
            }
            if (!empty($pictures)) {
                $writtenItem = $this->bindListingImages($writtenItem, $images, $pictures);
            }

            $written->push($writtenItem);
        }

        $this->log(DataObject::getSchema()->baseDataClass($written->dataClass()) . ' wrote: ' . $written->count());

        $this->extend('onAfterWriteListings', $written);

        return $written;
    }

    /**
     * @param Listing $listing
     * @param SS_List $dealers
     * @return Listing
     */
    protected function bindListingDealer(Listing $listing, SS_List $dealers): Listing
    {
        if (($dealerId = $listing->DealerId) && ($dealer = $dealers->find('ExternalID', $dealerId))) {
            unset($listing->DealerId);
            $listing->DealerID = $dealer->ID;
        }

        return $listing;
    }

    /**
     * @param Listing $listing
     * @param SS_List $makes
     * @return Listing
     */
    protected function bindListingMake(Listing $listing, SS_List $makes): Listing
    {
        if (($make = $listing->Make) && ($makeItem = $makes->find('Title', $make))) {
            unset($listing->Make);
            $listing->MakeID = $makeItem->ID;
        }

        return $listing;
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

        $this->log(DataObject::getSchema()->baseDataClass($written->dataClass()) . ' wrote: ' . $written->count());

        return $written;
    }

    /**
     * @param $item
     * @return DataObject
     */
    protected function writeItem(DataObject $item): DataObject
    {
        $existingItem = null;
        $uniqueField = $this->getUniqueIdentifier($item);
        if (($uniqueFieldValue = $item->{$uniqueField}) && $uniqueField) {
            $existingItem = $this->handleExistingItem($item, $uniqueField, $uniqueFieldValue);
        }
        if ($existingItem) {
            return $existingItem;
        }

        $this->extend('onBeforeWriteItem', $item);

        try {
            $item->write();
            $this->log('Wrote item ' . $item->ClassName . ' ' . $item->getTitle());
        } catch (\Exception $e) {
            $error = 'Error with' . $item->ClassName . ' , exception message: ' . $e->getMessage() . '. Data: ' . json_encode($item->toMap());
            $this->errors[] = $error;
        }

        $this->extend('onAfterWriteItem', $item);

        return $item;
    }

    /**
     * @param $item
     * @param $field
     * @param $uniqueFieldValue
     * @return DataObject|null
     */
    protected function handleExistingItem(DataObject $item, string $field, string $uniqueFieldValue): ?DataObject
    {
        $className = DataObject::getSchema()->baseDataClass($item->ClassName);
        $existing = $className::get()->filter([$field => $uniqueFieldValue])->first();
        // attempt to find existing item
        if ($existing) {
            $newData = $item->toMap();

            return $this->updateItem($existing, $newData);
        }

        return null;
    }

    /**
     * @param DataObject $existingItem
     * @param array      $data
     * @return DataObject
     */
    protected function updateItem(DataObject $existingItem, array $data): DataObject
    {
        // Compare Modified date to know if we want to update or not
        $extModified = $data['ModifiedDate'] ?? null;
        if ($extModified) { // check if it has a ModifiedDate field
            $extModified = new \DateTime($extModified);
            $localModified = new \DateTime($existingItem->ExternalModifiedDate);

            if ($extModified <= $localModified) {
                return $existingItem;
            }
        }

        $existingItem->update($data);

        $this->extend('onBeforeUpdateItem', $item);

        try {
            $existingItem->write();
            $this->log('Updated item ' . $existingItem->ClassName . ' ' . $existingItem->getTitle());
        } catch (\Exception $e) {
            $error = 'Error with' . $item->ClassName . '(ID ' . $item->ID . ') , exception message: ' . $e->getMessage();
            $this->errors[] = $error;
        }

        $this->extend('onAfterUpdateItem', $item);

        return $existingItem;
    }

    /**
     * @param DataObject $model
     * @return string|null
     */
    protected function getUniqueIdentifier(DataObject $model): ?string
    {
        $identifiers = self::config()->get('unique_field_identifiers');
        $className = DataObject::getSchema()->baseDataClass($model);
        if (isset($identifiers[$className])) {
            return $identifiers[$className];
        }

        return null;
    }

    protected function log($msg)
    {
        if (!Director::is_cli()) {
            echo $msg;
            echo '<br>';
        }
    }
}