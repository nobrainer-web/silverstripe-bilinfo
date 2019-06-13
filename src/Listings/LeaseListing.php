<?php


namespace NobrainerWeb\Bilinfo\Listings;


use SilverStripe\ORM\DataObject;

class LeaseListing extends DataObject
{
    private static $table_name = 'NW_BI_LeaseListing';

    /**
     * List of database fields. {@link DataObject::$db}
     *
     * @var array
     */
    private static $db = [
        'LeasingType'                  => 'Varchar',
        'LeasingDuration'              => 'Varchar',
        'LeasingDisclaimer'            => 'Varchar',
        'LeasingDownPayment'           => 'Varchar',
        'LeasingTotalPayment'          => 'Varchar',
        'LeasingResidualValue'         => 'Varchar',
        'LeasingYearlyMileage'         => 'Varchar',
        'LeasingIncludesInsurance'     => 'Varchar',
        'LeasingServiceAndMaintenance' => 'Varchar',
    ];
}