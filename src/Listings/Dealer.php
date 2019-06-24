<?php


namespace NobrainerWeb\Bilinfo\Listings;


use NobrainerWeb\Bilinfo\Listings\Access\ListingPermissions;
use SilverStripe\ORM\DataObject;

class Dealer extends DataObject
{
    use ListingPermissions;
    
    private static $table_name = 'NW_BI_Dealer';

    /**
     * List of database fields. {@link DataObject::$db}
     *
     * @var array
     */
    private static $db = [
        'ExternalID'  => 'Int', // "DealerId" field at Bilinfo,
        'Name'        => 'Varchar',
        'StreetLine1' => 'Varchar',
        'StreetLine2' => 'Varchar',
        'Zip'         => 'Varchar(15)',
        'City'        => 'Varchar',
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