<?php


namespace NobrainerWeb\Bilinfo\Listings;

use NobrainerWeb\Bilinfo\Interfaces\Listing as ListingInterface;
use SilverStripe\ORM\DataObject;

class Listing extends DataObject implements ListingInterface
{
    private static $table_name = 'NW_BI_Listing';
    private static $singular_name = 'Vehicle listing';
    private static $plural_name = 'Vehicle listings';
    private static $description = 'Represents the sales/lease listing of a vehicle';

    /**
     * List of database fields. {@link DataObject::$db}
     *
     * TODO map all relevant API fields
     *
     * @var array
     */
    private static $db = [
        'ExternalID'           => 'Int', // "Id" field at Bilinfo,
        'ExternalModifiedDate' => 'Datetime',
        'ExternalCreatedDate'  => 'Datetime',
        'ExternalDeletedDate'  => 'Datetime',
        'VehicleId'            => 'Varchar(200)',
        'Price'                => 'Varchar',
        // describing fields (all fields are strings in the API response)
        'Mileage'              => 'Varchar',
        'Year'                 => 'Varchar',
        'ProductionYear'       => 'Varchar',
        'Make'                 => 'Varchar',
        'Model'                => 'Varchar',
        'Variant'              => 'Varchar',
        'RegistrationDate'     => 'Varchar',
        'Type'                 => 'Varchar',
        'Motor'                => 'Varchar',
        'Propellant'           => 'Varchar',
        'NumberOfDoors'        => 'Varchar',
        'NewPrice'             => 'Varchar',
        'NumberOfGears'        => 'Varchar',
        'GearType'             => 'Varchar',
        'MotorVolume'          => 'Varchar',
        'Effect'               => 'Varchar',
        'Cylinders'            => 'Varchar',
        'ValvesPerCylinder'    => 'Varchar',
        'DriveWheels'          => 'Varchar',
        'TrailerWeight'        => 'Varchar',
        'GasTankMax'           => 'Varchar',
        'KmPerLiter'           => 'Varchar',
        'Acceleration0To100'   => 'Varchar',
        'TopSpeed'             => 'Varchar',
        'EffectInNm'           => 'Varchar',
        'EffectInNmRpm'        => 'Varchar',
        'Weight'               => 'Varchar',
        'GreenTax'             => 'Varchar',
        'GreenTaxPeriod'       => 'Varchar',
        'WeightTax'            => 'Varchar',
        'WeightTaxPeriod'      => 'Varchar',
        'Payload'              => 'Varchar',
        'NumberOfAirbags'      => 'Varchar',
        'TotalWeight'          => 'Varchar',
        'Length'               => 'Varchar',
        'Width'                => 'Varchar',
        'Height'               => 'Varchar',
        'BodyType'             => 'Varchar',
        'Comment'              => 'Text',
    ];

    /**
     * List of one-to-one relationships. {@link DataObject::$has_one}
     *
     * @var array
     */
    private static $has_one = [
        'Dealer' => Dealer::class,
        'Make'   => Make::class
    ];

    /**
     * List of one-to-many relationships. {@link DataObject::$has_many}
     *
     * @var array
     */
    private static $has_many = [
        'ListingImages' => ListingImage::class
    ];

    /**
     * List of many-to-many relationships. {@link DataObject::$many_many}
     *
     * @var array
     */
    private static $many_many = [
        'Equipment' => Equipment::class
    ];

    public function getTitle()
    {
        return $this->VehicleId;
    }

    /**
     * @return bool
     */
    public function isSold(): bool
    {
        return (bool)$this->ExternalDeletedDate;
    }
}