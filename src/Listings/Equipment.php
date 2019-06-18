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

    protected function onBeforeDelete()
    {
        // TODO clean up relation table
        // this somehow gives a db lock
        //$this->Listings()->removeAll();
        parent::onBeforeDelete();
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        if(!$this->Title){
            return null;
        }

        return _t(__CLASS__ . '.' . $this->Title, $this->getTitle());
    }
}














































