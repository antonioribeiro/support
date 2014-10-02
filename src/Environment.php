<?php

namespace PragmaRX\Support;

use Exception;

class Environment {

	/**
	 * Is the .environment file loaded?
	 *
	 * @var bool
	 */
	protected static $loaded = false;

	/**
	 * Load the environment file and return a closure to get current environment.
	 *
	 * @param null $file
	 * @return callable
	 * @throws Exception
	 */
	public static function getDetectionClosure($file = null)
	{
		static::load($file);

		return function() { return env('LARAVEL_ENV'); };
	}

	/**
	 * Load the environment file.
	 *
	 * @param null $file
	 * @throws Exception
	 */
	public static function load($file = null)
	{
		if ( ! static::$loaded)
		{
			if ( ! file_exists($file))
			{
				throw new Exception('Environment file does not exists: '.$file);
			}

			foreach(require $file as $key => $value)
			{
				if ($value === true)
				{
					$value = '(true)';
				}
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

}
