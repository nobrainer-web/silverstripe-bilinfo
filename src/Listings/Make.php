<?php


namespace NobrainerWeb\Bilinfo\Listings;


use NobrainerWeb\Bilinfo\Listings\Access\ListingPermissions;
use SilverStripe\ORM\DataObject;

class Make extends DataObject
{
    use ListingPermissions;
    
    private static $table_name = 'NW_BI_Make';
    private static $singular_name = 'Vehicle make';
    private static $plural_name = 'Vehicle make';

    /**
     * List of database fields. {@link DataObject::$db}
     *
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar'
    ];

    /**
     * List of one-to-many relationships. {@link DataObject::$has_many}
     *
     * @var array
     */
    private static $has_many = [
        'Listings' => Listing::class
    ];
}