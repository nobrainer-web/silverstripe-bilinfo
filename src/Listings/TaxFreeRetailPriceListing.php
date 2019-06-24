<?php


namespace NobrainerWeb\Bilinfo\Listings;

use NobrainerWeb\Bilinfo\Interfaces\Listing as ListingInterface;
use SilverStripe\ORM\DataObject;

class TaxFreeRetailPriceListing extends Listing implements ListingInterface
{
    private static $table_name = 'NW_BI_TaxFreeRetailPriceListing';
    private static $singular_name = 'Tax-free Retail price listing';
    private static $plural_name = 'Tax-free Retail price listings';
}