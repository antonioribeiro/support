<?php

/**
 * Part of the Support package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Support
 * @version    1.0.0
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 */

namespace PragmaRX\Support;

class GeoIp {

	private $enabled = true;

	private $handle;

	public function byAddr($addr)
	{
		if ( ! $this->enabled || ! $this->isGeoIpAvailable())
		{
			return;
		}

		$this->open();

		$data = $this->generateGeoIpData($addr);

		$this->close();

		return $data;
	}

	/**
	 * @return boolean
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @param boolean $enabled
	 */
	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;
	}

	public function isGeoIpAvailable()
	{
		if ( ! function_exists('geoip_country_code_by_addr'))
		{
			return false;
		}

		return true;
	}

	private function open()
	{
		$file = __DIR__."/geoip/GeoLiteCity.dat";

		if ( ! file_exists($file))
		{
			return false;
		}

		$this->handle = geoip_open($file, GEOIP_STANDARD);
	}

	private function close()
	{
		if($this->handle)
		{
			geoip_close($this->handle);
		}
	}

	private function generateGeoIpData($addr)
	{
		return (array) geoip_record_by_addr($this->handle, $addr);
	}
}