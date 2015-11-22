<?php

namespace PragmaRX\Support\GeoIp;

class GeoIp1 extends GeoIpAbstract implements GeoIpContract
{
    public function searchAddr($addr) {
        if ( ! $this->isEnabled() || ! $this->isGeoIpAvailable()) {
            return;
        }

        $this->open();

        $this->geoIpData = $this->generateGeoIpData($addr);

        $this->close();

        return $this->geoIpData;
    }

    public function isGeoIpAvailable() {
        if (!function_exists('geoip_country_code_by_addr')) {
            return false;
        }

        return true;
    }

    private function open() {
        $file = $this->getGeoliteFileName();

        if (!file_exists($file)) {
            return false;
        }

        $this->handle = geoip_open($file, GEOIP_STANDARD);
    }

    private function close() {
        if ($this->handle) {
            geoip_close($this->handle);
        }
    }

    private function generateGeoIpData($addr) {
        return (array) geoip_record_by_addr($this->handle, $addr);
    }

    /**
     * @return string
     */
    private function getGeoliteFileName() {
        return __DIR__ . "/GeoLiteCity.dat";
    }
}
