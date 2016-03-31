<?php

namespace PragmaRX\Support;

use Exception;
use PragmaRX\Support\Exceptions\EnvironmentVariableNotSet;

class Environment {

	const APP_ENV = 'APP_ENV';

	/**
	 * Is the .environment file loaded?
	 *
	 * @var bool
	 */
	protected static $hasBeenLoaded = false;

	/**
	 * Bypassed environment variables?
	 *
	 * @var bool
	 */
	private static $bypassed = [];

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

		return function() { return self::name() ; };
	}

	/**
	 * Load the environment file.
	 *
	 * @param null $file
	 * @throws Exception
	 */
	public static function load($file = null)
	{
		if ( ! static::$hasBeenLoaded)
		{
			if ( ! file_exists($file))
			{
				static::raiseEnvironmentVariableNotSet();
			}

			$data = require $file;
			foreach ($data as $key => $value)
			{
			    putenv(sprintf('%s=%s', $key, static::toString($value)));
			}

			static::$hasBeenLoaded = true;
		}
	}

	/**
	 * @param $variable
	 * @param string $default
	 * @return bool|null|string
	 * @throws EnvironmentVariableNotSet
	 */
	public static function getOrRaise($variable, $default = '#default#')
	{
		static::setAppEnv();

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
			if ($default === '#default#')
			{
				static::raiseEnvironmentVariableNotSet();
			}

			return $default;
		}

		return static::fromString($value);
	}

	public static function bypass($variable, $value)
	{
		static::$bypassed[$variable] = static::toString($value);

		static::setAppEnv();
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

	public static function name()
	{
		$host = explode('.', static::getHostname())[0];

		if (in_array($host, ['testing', 'local', 'development', 'production', 'staging']))
		{
			return $host;
		}

		if (isset(static::$bypassed[static::APP_ENV]))
		{
			return static::$bypassed[static::APP_ENV];
		}

		return getenv(static::APP_ENV);
	}

	private static function setAppEnv()
	{
		$env = static::name();

		putenv(static::APP_ENV.'='.$env);

		app()['env'] = $env;
	}

	private static function logAvailable()
	{
		return app()->bound('log');
	}

	private static function getHostname()
	{
		if (app()->bound('request'))
		{
			if (app()['request'] instanceof ArgvInput)
			{
				return 'localhost';
			}

			return app()['request']->server->get('HTTP_HOST');
		}

		return 'nohost';
	}

	private static function raiseEnvironmentVariableNotSet()
	{
		// Temporarily disabled. Let's find a better way to inform?
		//
		// if (static::logAvailable())
		// {
		// 	// throw new EnvironmentVariableNotSet("Environment variable not set: $variable");
		// }
		// else
		// {
		// 	dd("Environment variable not set: $variable");
		// }
	}

}
