<?php

namespace PragmaRX\Support\GeoIp;

class GeoIpAbstract
{
    protected $enabled = true;

    protected $handle;

    protected $geoIpData;

    /**
     * @return boolean
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }
}
