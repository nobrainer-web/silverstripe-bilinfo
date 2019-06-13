<?php


namespace NobrainerWeb\Bilinfo\Listings;


use SilverStripe\ORM\DataObject;

class Dealer extends DataObject
{
    private static $table_name = 'NW_BI_Dealer';

    /**
     * List of one-to-many relationships. {@link DataObject::$has_many}
     *
     * @var array
     */
    private static $has_many = [
        'Listings' => Listing::class
    ];
}