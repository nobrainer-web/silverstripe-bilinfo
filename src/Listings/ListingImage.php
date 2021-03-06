<?php


namespace NobrainerWeb\Bilinfo\Listings;


use NobrainerWeb\Bilinfo\Listings\Access\ListingPermissions;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;

class ListingImage extends DataObject
{
    use ListingPermissions;

    private static $table_name = 'NW_BI_ListingImage';
    private static $description = 'Represents external image belonging to a vehicle listing';

    /**
     * List of database fields. {@link DataObject::$db}
     *
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar',
        'URL'   => 'Varchar'
    ];

    private static $summary_fields = [
        'getSummaryImage' => 'Thumbnail',
        'Title',
        'URL'
    ];

    private static $searchable_fields = [
        'Title',
        'URL'
    ];

    /**
     * List of one-to-one relationships. {@link DataObject::$has_one}
     *
     * @var array
     */
    private static $has_one = [
        'Listing' => Listing::class
    ];

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (($url = $this->URL) && !$this->Title) {
            $parts = explode('/', $url);
            $this->Title = end($parts);
        }
    }

    /**
     * @return DBHTMLText
     */
    public function getSummaryImage(): DBHTMLText
    {
        return $this->customise(['Image' => $this])->renderWith(__NAMESPACE__ . '/SummaryImage');
    }
}