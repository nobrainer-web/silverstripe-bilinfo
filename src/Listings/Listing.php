<?php


namespace NobrainerWeb\Bilinfo\Listings;

use NobrainerWeb\Bilinfo\Interfaces\Listing as ListingInterface;
use NobrainerWeb\Bilinfo\Listings\Access\ListingPermissions;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\Filters\ExactMatchFilter;
use SilverStripe\ORM\Filters\PartialMatchFilter;
use SilverStripe\Security\PermissionProvider;

class Listing extends DataObject implements ListingInterface, PermissionProvider
{
    use ListingPermissions;

    private static $table_name = 'NW_BI_Listing';
    private static $singular_name = 'Vehicle listing';
    private static $plural_name = 'Vehicle listings';
    private static $description = 'Represents the sales/lease listing of a vehicle';
    private static $default_sort = 'Make, Model';

    /**
     * List of database fields. {@link DataObject::$db}
     *
     * NOTE: Some of these fields might be of INT datatype, but the api itself only returns all values as strings. These castings to INT are assumptions
     *
     * @var array
     */
    private static $db = [
        'ExternalID'           => 'Int', // "Id" field at Bilinfo,
        'ExternalModifiedDate' => 'Datetime',
        'ExternalCreatedDate'  => 'Datetime',
        'ExternalDeletedDate'  => 'Datetime',
        'VehicleId'            => 'Varchar(200)',
        'Price'                => 'Int',
        // describing fields (all fields are strings in the API response)
        'Mileage'              => 'Int',
        'Year'                 => 'Year',
        'ProductionYear'       => 'Year',
        'Make'                 => 'Varchar',
        'Model'                => 'Varchar',
        'Variant'              => 'Varchar',
        'RegistrationDate'     => 'Varchar',
        'Type'                 => 'Varchar',
        'Motor'                => 'Varchar',
        'Propellant'           => 'Varchar',
        'NumberOfDoors'        => 'Int',
        'NewPrice'             => 'Varchar',
        'NumberOfGears'        => 'Int',
        'GearType'             => 'Varchar',
        'MotorVolume'          => 'Varchar',
        'Effect'               => 'Varchar',
        'Cylinders'            => 'Int',
        'ValvesPerCylinder'    => 'Int',
        'DriveWheels'          => 'Varchar',
        'TrailerWeight'        => 'Int',
        'GasTankMax'           => 'Varchar',
        'KmPerLiter'           => 'Varchar',
        'Acceleration0To100'   => 'Varchar',
        'TopSpeed'             => 'Int',
        'EffectInNm'           => 'Int',
        'EffectInNmRpm'        => 'Int',
        'Weight'               => 'Int',
        'GreenTax'             => 'Varchar',
        'GreenTaxPeriod'       => 'Varchar',
        'WeightTax'            => 'Int',
        'WeightTaxPeriod'      => 'Int',
        'Payload'              => 'Int',
        'NumberOfAirbags'      => 'Int',
        'TotalWeight'          => 'Int',
        'Length'               => 'Int',
        'Width'                => 'Int',
        'Height'               => 'Int',
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

    /**
     * @var array
     */
    private static $summary_fields = [
        'getSummaryImages' => 'Image',
        'Model'            => 'Model',
        'Variant'          => 'Variant',
        'Year'             => 'Year',
        'Make.Title'       => 'Make',
        'Dealer.Title'     => 'Dealer',
        'getTypeName'      => 'Type',
    ];

    /**
     * @var array
     */
    private static $searchable_fields = [
        'ExternalID' => ExactMatchFilter::class,
        'Model'      => PartialMatchFilter::class,
        'Variant'    => PartialMatchFilter::class,
        'DealerID'   => ExactMatchFilter::class,
        'MakeID'     => ExactMatchFilter::class,
        'Year'       => ExactMatchFilter::class,
        'ClassName'  => ExactMatchFilter::class
    ];

    /**
     * @return \SilverStripe\ORM\Search\SearchContext
     */
    public function getDefaultSearchContext()
    {
        $context = parent::getDefaultSearchContext();
        $fields = $context->getFields();

        // set proper human readable values for ClassName filter field
        /** @var DropdownField $classNameField */
        if ($classNameField = $fields->dataFieldByName('ClassName')) {
            $src = $classNameField->getSource();
            $classes = ClassInfo::getValidSubClasses(__CLASS__);

            foreach ($classes as $className) {
                $single = $className::singleton();
                $src[$className] = $single->i18n_singular_name();
            }
            $classNameField->setSource($src);
        }

        return $context;
    }

    /**
     * @return array
     */
    public function providePermissions()
    {
        return array(
            'BI_LISTING_CREATE' => [
                'name'     => 'Create a vehicle listing',
                'category' => 'Bil Info'
            ],
            'BI_LISTING_EDIT'   => [
                'name'     => 'Edit a vehicle listing',
                'category' => 'Bil Info',
            ],
            'BI_LISTING_DELETE' => [
                'name'     => 'Delete a vehicle listing',
                'category' => 'Bil Info',
            ]
        );
    }

    /***
     * @return string
     */
    public function getTitle(): string
    {
        if($title = $this->getField('Title')){
            return $title;
        }

        $title = $this->Make()->exists() ? $this->Make()->Title : '';
        $title .= ' ' . $this->Model . ' ' . $this->Variant . ' ' . $this->Year;

        $this->extend('updateTitle', $title);

        return $title;
    }

    /**
     * @return bool
     */
    public function isSold(): bool
    {
        return (bool)$this->ExternalDeletedDate;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return $this->i18n_singular_name();
    }

    /**
     * @return DBHTMLText
     */
    public function getSummaryImages(): DBHTMLText
    {
        return $this->customise(['Image' => $this->ListingImages()->first()])->renderWith(__NAMESPACE__ . '/SummaryImage');
    }
}