<?php

namespace PragmaRX\Support\GeoIp;

use GeoIp2\Database\Reader as GeoIpReader;

class GeoIp
{
    private $geoIp;

    /**
     * @var null
     */
    private $databasePath;

    public function __construct($databasePath = null)
    {
        $this->databasePath = $databasePath;
    }

    private function databaseExists()
    {
        return file_exists($this->databasePath);
    }

    private function getGeoIp()
    {
        if (! $this->geoIp && $this->databaseExists()) {
            $this->geoIp = $this->getGeoIpInstance($this->databasePath);
        }

        return $this->geoIp;
    }

    public function searchAddr($addr) {
        if ($geoip = $this->getGeoIp()) {
            return $geoip->searchAddr($addr);
        }
    }

    /**
     * @return boolean
     */
    public function isEnabled() {
        return $this->getGeoIp()->isEnabled();
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled) {
        return $this->getGeoIp()->setEnabled($enabled);
    }

    public function isGeoIpAvailable() {
        return $this->getGeoIp()->isGeoIpAvailable();
    }

    private function getGeoIpInstance($databasePath = null) {
        if (class_exists(GeoIpReader::class))
        {
            return new GeoIp2($databasePath);
        }

        return new GeoIp1();
    }
}
