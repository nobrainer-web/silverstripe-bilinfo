<?php


namespace NobrainerWeb\Bilinfo\Listings;

use NobrainerWeb\Bilinfo\Interfaces\Listing as ListingInterface;
use SilverStripe\ORM\DataObject;

class RetailPriceListing extends Listing implements ListingInterface
{
    private static $table_name = 'NW_BI_RetailPriceListing';
    private static $singular_name = 'Retail price listing';
    private static $plural_name = 'Retail price listings';
}