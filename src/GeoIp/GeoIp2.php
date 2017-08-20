<?php

namespace PragmaRX\Support\GeoIp;

use GeoIp2\Database\Reader as GeoIpReader;
use GeoIp2\Exception\AddressNotFoundException;

class GeoIp2 extends GeoIpAbstract implements GeoIpContract
{
    const DATABASE_FILE_NAME = 'GeoLite2-City.mmdb';

    private $reader;

    public function __construct($databasePath = null)
    {
        $this->reader = new GeoIpReader($this->getGeoliteFileName($databasePath));
    }

    public function searchAddr($addr) {
        if ( ! $this->isEnabled()) {
            return;
        }

        if ($this->geoIpData = $this->getCity($addr))
        {
            return $this->renderData();
        }

        return null;
    }

    /**
     * Get the GeoIp database file name and path.
     *
     * @param null $databasePath
     * @return string
     */
    private function getGeoliteFileName($databasePath = null) {
        return ($databasePath ?: __DIR__) . DIRECTORY_SEPARATOR . static::DATABASE_FILE_NAME;
    }

    private function renderData() {
        return [
            'latitude' => $this->geoIpData->location->latitude,
            'longitude' => $this->geoIpData->location->longitude,
            'country_code' => $this->geoIpData->country->isoCode,
            'country_code3' => null,
            'country_name' => $this->geoIpData->country->name,
            'region' => $this->geoIpData->continent->code,
            'city' => $this->geoIpData->city->name,
            'postal_code' => $this->geoIpData->postal->code,
            'area_code' => null,
            'dma_code' => null,
            'metro_code' => $this->geoIpData->location->metroCode,
            'continent_code' => $this->geoIpData->continent->code,
        ];
    }

    /**
     * @param $addr
     * @return \GeoIp2\Model\City
     */
    private function getCity($addr) {
        try
        {
            $city = $this->reader->city($addr);
        }
        catch (AddressNotFoundException $e)
        {
            $city = null;
        }

        return $city;
    }
}
