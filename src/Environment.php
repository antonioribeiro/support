<?php

namespace PragmaRX\Support;

use Exception;

class Environment {

	protected static $loaded = false;

	public static function load($file = null)
	{
		if ( ! static::$loaded)
		{
			if ( ! file_exists($file))
			{
				throw new Exception('Environment file (.environment) was not set or does not exists: '.$file);
			}

			foreach(require $file as $key => $value)
			{
				if ($value === false)
				{
					$value = '(false)';
				}
				else
				if ($value === null)
				{
					$value = '(null)';
				}
				else
				if (empty($value))
				{
					$value = '(empty)';
				}

			    putenv(sprintf('%s=%s', $key, $value));
			}

			static::$loaded = true;
		}
	}

	public static function getDetectionClosure($file = null)
	{
		static::load($file);

		return function() { return getenv('LARAVEL_ENV'); };
	}
}
