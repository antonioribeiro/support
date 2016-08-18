<?php

namespace PragmaRX\Support\GeoIp;

use GeoIp2\Database\Reader as GeoIpReader;

class GeoIp
{
    private $geoIp;

    private function getGeoIp()
    {
        if (! $this->geoIp) {
            $this->geoIp = $this->getGeoIpInstance();
        }

        return $this->geoIp;
    }

    public function searchAddr($addr) {
        return $this->getGeoIp()->searchAddr($addr);
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

    private function getGeoIpInstance() {
        if (class_exists(GeoIpReader::class))
        {
            return new GeoIp2();
        }

        return new GeoIp1();
    }
}
