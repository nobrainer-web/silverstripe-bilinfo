<?php


namespace NobrainerWeb\Bilinfo\Listings;


use SilverStripe\ORM\DataObject;

class Equipment extends DataObject
{
    private static $table_name = 'NW_BI_Equipment';
    private static $singular_name = 'Vehicle equipment';
    private static $plural_name = 'Vehicle equipment';
    private static $description = 'Represents extra equipment of a vehicle';

    /**
     * List of database fields. {@link DataObject::$db}
     *
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar'
    ];

    private static $belongs_many_many = [
        'Listings' => Listing::class
    ];
}