<?php


namespace NobrainerWeb\Bilinfo\Listings;

use NobrainerWeb\Bilinfo\Interfaces\Listing as ListingInterface;
use SilverStripe\ORM\DataObject;

class LeaseListing extends Listing implements ListingInterface
{
    private static $table_name = 'NW_BI_LeaseListing';
    private static $singular_name = 'Lease listing';
    private static $plural_name = 'Lease listings';

    /**
     * List of database fields. {@link DataObject::$db}
     *
     * @var array
     */
    private static $db = [
        'LeasingType'                  => 'Varchar',
        'LeasingDuration'              => 'Varchar',
        'LeasingAudience'              => 'Varchar',
        'LeasingDisclaimer'            => 'Varchar',
        'LeasingDownPayment'           => 'Varchar',
        'LeasingTotalPayment'          => 'Varchar',
        'LeasingResidualValue'         => 'Varchar',
        'LeasingYearlyMileage'         => 'Varchar',
        'LeasingIncludesInsurance'     => 'Varchar',
        'LeasingServiceAndMaintenance' => 'Varchar',
    ];
}