<?php
/**
 * Created by PhpStorm.
 * User: AntonioCarlos
 * Date: 22/11/2015
 * Time: 01:44
 */

namespace PragmaRX\Support\GeoIp;


interface GeoIpContract
{
    public function searchAddr($addr);
}
