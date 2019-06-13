<?php


namespace NobrainerWeb\Bilinfo\Listings;


use SilverStripe\ORM\DataObject;

class Make extends DataObject
{
    private static $table_name = 'NW_BI_Make';
    private static $singular_name = 'Vehicle make';
    private static $plural_name = 'Vehicle make';

    /**
     * List of one-to-many relationships. {@link DataObject::$has_many}
     *
     * @var array
     */
    private static $has_many = [
        'Listings' => Listing::class
    ];
}