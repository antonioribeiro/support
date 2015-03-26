<?php

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
		$file = __DIR__ . "/geoip/GeoLiteCity.dat";

		if ( ! file_exists($file))
		{
			return false;
		}

		$this->handle = geoip_open($file, GEOIP_STANDARD);
	}

	private function close()
	{
		if ($this->handle)
		{
			geoip_close($this->handle);
		}
	}

	private function generateGeoIpData($addr)
	{
		return (array) geoip_record_by_addr($this->handle, $addr);
	}
}
