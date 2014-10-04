<?php

namespace PragmaRX\Support;

use Exception;
use PragmaRX\Support\Exceptions\EnvironmentVariableNotSet;

class Environment {

	private static $bypassed = array();

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
			    putenv(sprintf('%s=%s', $key, static::toString($value)));
			}

			static::$loaded = true;
		}
	}

	public static function get($variable)
	{
		// If you need somehow to bypass the environment, just create this helper function

		if (isset(static::$bypassed[$variable]))
		{
			$value = static::$bypassed[$variable];
		}

		if ( ! isset($value))
		{
			$value = getenv($variable);
		}

		if ($value == false || empty($value))
		{
			throw new EnvironmentVariableNotSet("Environment variable not set: $variable");
		}

		return static::fromString($value);
	}

	public static function bypass($variable, $value)
	{
		static::$bypassed[$variable] = static::toString($value);
	}

	private static function toString($value)
	{
		if ($value === true)
		{
			$value = '(true)';
		}
		if ($value === false)
		{
			$value = '(false)';
		}
		elseif ($value === null)
		{
			$value = '(null)';
		}
		elseif (empty($value))
		{
			$value = '(empty)';
		}

		return $value;
	}

	private static function fromString($value)
	{
		if ($value === 'true' || $value === '(true)')
		{
			$value = true;
		}
		elseif ($value === 'false' || $value === '(false)')
		{
			$value = false;
		}
		elseif ($value === '(null)')
		{
			$value = null;
		}
		elseif ($value === '(empty)')
		{
			$value = '';
		}

		return $value;
	}
}
