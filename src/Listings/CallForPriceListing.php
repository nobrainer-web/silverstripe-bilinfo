<?php


namespace NobrainerWeb\Bilinfo\Listings;

use NobrainerWeb\Bilinfo\Interfaces\Listing as ListingInterface;
use SilverStripe\ORM\DataObject;

class CallForPriceListing extends Listing implements ListingInterface
{
    private static $table_name = 'NW_BI_CallForPriceListing';
}