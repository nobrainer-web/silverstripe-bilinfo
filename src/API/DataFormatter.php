<?php


namespace NobrainerWeb\Bilinfo\API;


use SilverStripe\Core\Injector\Injectable;

class DataFormatter
{
    use Injectable;

    /**
     * @var string (json from API response)
     */
    protected $data;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    public function output(): array
    {
        //TODO do json decode of data
    }
}