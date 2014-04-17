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
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 */

namespace PragmaRX\Support;


class Timer {

	/**
	 * Started at time.
	 *
	 * @var
	 */
	private static $startedAt = [];

	/**
	 * Stopped at time.
	 *
	 * @var
	 */
	private static $stoppedAt = [];

	/**
	 * An internal instance of this class.
	 *
	 * @var
	 */
	private static $instance;

	/**
	 * Start a timer.
	 *
	 * @param string $timer
	 * @internal param $
	 */
	private function start($timer = 'default')
	{
		static::$startedAt[$timer] = microtime(true);

		static::$stoppedAt[$timer] = null;
	}

	/**
	 * Stop a timer.
	 *
	 * @param string $timer
	 * @internal param $
	 */
	private function stop($timer = 'default')
	{
		static::$stoppedAt[$timer] = microtime(true);
	}

	/**
	 * Check if timer is started.
	 *
	 * @param string $timer
	 * @return bool
	 */
	private function timerStarted($timer = 'default')
	{
		return ! is_null(static::$startedAt[$timer]);
	}

	/**
	 * Check if timer is stopped.
	 *
	 * @param string $timer
	 * @return bool
	 */
	private function timerStopped($timer = 'default')
	{
		return ! is_null(static::$stoppedAt[$timer]);
	}

	/**
	 * Get elapsed time in microtime.
	 *
	 * @param string $timer
	 * @param bool $stop
	 * @return number
	 */
	private function getElapsedRaw($timer = 'default', $stop = true)
	{
		if ($stop)
		{
			static::stop($timer);
		}

		$end = static::timerStopped($timer)
				? static::$stoppedAt[$timer]
				: microtime(true);

		return abs($end - static::$startedAt[$timer]);
	}

	/**
	 * Get the elapsed time in string.
	 *
	 * @param string $timer
	 * @param bool $stop
	 * @return string
	 */
	private function getElapsedTime($timer = 'default', $stop = true)
	{
		return sprintf("%.4f", static::getElapsedRaw($timer, $stop));
	}

	public static function __callStatic($name, array $arguments)
	{
		if ( ! static::$instance)
		{
			$class = __CLASS__;

			static::$instance = new $class;
		}

		return call_user_func_array([static::$instance, $name], $arguments);
	}

	public function __call($name, array $arguments)
	{
		return call_user_func_array([$this, $name], $arguments);
	}
}