<?php


namespace NobrainerWeb\Bilinfo\API;


use NobrainerWeb\Bilinfo\Listings\Dealer;
use NobrainerWeb\Bilinfo\Listings\Equipment;
use NobrainerWeb\Bilinfo\Listings\Listing;
use NobrainerWeb\Bilinfo\Listings\ListingImage;
use NobrainerWeb\Bilinfo\Listings\Make;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\ArrayList;

class DataMapper
{
    use Configurable;
    use Injectable;

    /**
     * Response data as array
     *
     * @var array
     */
    protected $data = [];

    private static $listing_fields_map = [
        'Id'                 => 'ExternalID',
    ];

    private static $dealer_fields_map = [
        'DealerId'                 => 'ExternalID',
        'DealerName'               => 'Name',
        'DealerAddressStreetLine1' => 'StreetLine1',
        'DealerAddressStreetLine2' => 'StreetLine2',
        'DealerAddressZipCode'     => 'Zip',
        'DealerAddressCity'        => 'City',
    ];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $make
     * @return Make
     */
    public function mapMake(string $make): Make
    {
        return Make::create(['Title' => $make]);
    }

    /**
     * @return ArrayList
     */
    public function mapMakes(): ArrayList
    {
        $list = ArrayList::create();

        foreach ($this->data as $listing) {
            $make = $listing['Make'];
            if(!$make){
                continue;
            }
            if (!$list->find('Title', $make)) {
                $list->push($this->mapMake($make));
            }
        }

        return $list;
    }

    /**
     * @param string $name
     * @return Equipment
     */
    public function mapEquipmentItem(string $name): Equipment
    {
        return Equipment::create(['Title' => $name]);
    }

    /**
     * @return ArrayList
     */
    public function mapEquipment(): ArrayList
    {
        $list = ArrayList::create();

        foreach ($this->data as $listing) {
            $equipment = $listing['EquipmentList'];
            if (!$equipment || empty($equipment)) {
                continue;
            }
            foreach ($equipment as $name) {
                if (!$list->find('Title', $name)) {
                    $list->push($this->mapEquipmentItem($name));
                }
            }
        }

        return $list;
    }

    /**
     * @param string $url
     * @return ListingImage
     */
    public function mapListingImage(string $url): ListingImage
    {
        return ListingImage::create(['URL' => $url]);
    }

    /**
     * @return ArrayList
     */
    public function mapListingImages(): ArrayList
    {
        $list = ArrayList::create();

        foreach ($this->data as $listing) {
            $images = $listing['Pictures'];
            if (!$images || empty($images)) {
                continue;
            }
            foreach ($images as $url) {
                $list->push($this->mapListingImage($url));
            }
        }

        return $list;
    }

    /**
     * @param array $data
     * @return Dealer|null
     */
    public function mapDealer(array $data): ?Dealer
    {
        $dealer = [];

        foreach (self::config()->get('dealer_fields_map') as $apiField => $localField) {
            if ($val = $data[$apiField]) {
                $dealer[$localField] = $val;
            }
        }

        if (empty($dealer)) {
            return null;
        }

        return Dealer::create($dealer);
    }

    /**
     * @return ArrayList
     */
    public function mapDealers(): ArrayList
    {
        $dealers = ArrayList::create();

        foreach ($this->data as $listing) {
            $dealer = $this->mapDealer($listing);
            if ($dealer && !$dealers->find('ExternalID', $dealer->ExternalID)) {
                $dealers->push($dealer);
            }
        }

        return $dealers;
    }

    /**
     * TODO creation of various types of listings
     * 
     * @param array $data
     * @return Listing
     */
    public function mapListing(array $data): Listing
    {
        $listing = [];

        // Map fields that do not have the same name locally as in the external APi
        foreach (self::config()->get('listing_fields_map') as $apiField => $localField) {
            if ($val = $data[$apiField]) {
                $listing[$localField] = $val;
            }
        }
        
        // might clash with our DB
        unset($data['Id']);
        
        $listing = array_merge($listing, $data);

        return Listing::create($listing);
    }

    /**
     * @return ArrayList
     */
    public function mapListings(): ArrayList
    {
        $list = ArrayList::create();

        foreach ($this->data as $listing) {
            $listing = $this->mapListing($listing);
            $list->push($listing);
        }

        return $list;
    }
}