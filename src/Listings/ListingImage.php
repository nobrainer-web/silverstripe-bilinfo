<?php


namespace NobrainerWeb\Bilinfo\Listings;


use SilverStripe\ORM\DataObject;

class ListingImage extends DataObject
{
    private static $table_name = 'NW_BI_ListingImage';
    private static $description = 'Represents external image belonging to a vehicle listing';

    /**
     * List of one-to-one relationships. {@link DataObject::$has_one}
     *
     * @var array
     */
    private static $has_one = [
        'Listing' => Listing::class
    ];
}