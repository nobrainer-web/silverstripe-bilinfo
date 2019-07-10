<?php


namespace NobrainerWeb\Bilinfo\Tasks;


use NobrainerWeb\Bilinfo\API\ListingsClient;
use NobrainerWeb\Bilinfo\Listings\Equipment;
use NobrainerWeb\Bilinfo\Listings\ListingImage;
use NobrainerWeb\Bilinfo\Listings\Make;

class GetSinceDaysDataTask extends GetApiDataTask
{
    protected $title = 'Bilinfo - Get data from the API with the ?sincedays param';

    private static $sincedays = 1;

    /**
     * @return array
     */
    protected function fetchData(): array
    {
        $sincedays = self::config()->get('sincedays');
        $client = ListingsClient::create();
        $client->setSinceDays($sincedays);
        $data = [];
        try {
            $data = $client->get();
        } catch (\Exception $e) {
            $this->log('ERROR ON DATA FETCH: ' . $e->getMessage());
            $this->extend('onFailedDataFetch', $e);

            return $data;
        }

        $this->extend('onAfterFetchData', $data);

        return $data;
    }
}