<?php

namespace PragmaRX\Support;


class Timer {

	/**
	 * Started at time.
	 *
	 * @var
	 */
	static $startedAt;

	/**
	 * Stopped at time.
	 *
	 * @var
	 */
	static $stoppedAt;

	/**
	 * Start a timer.
	 *
	 */
	public static function start()
	{
		static::$startedAt = microtime(true);

		static::$stoppedAt = null;
	}

	/**
	 * Stop a timer.
	 *
	 */
	public static function stop()
	{
		static::$stoppedAt = microtime(true);
	}

	/**
	 * Check if timer is started.
	 *
	 * @return bool
	 */
	public static function timerStarted()
	{
		return ! is_null(static::$startedAt);
	}

	/**
	 * Check if timer is stopped.
	 *
	 * @return bool
	 */
	public static function timerStopped()
	{
		return ! is_null(static::$stoppedAt);
	}

	/**
	 * Get elapsed time in microtime.
	 *
	 * @param $stop
	 * @return number
	 */
	public static function getElapsedRaw($stop = true)
	{
		if ($stop)
		{
			static::stop();
		}

		$end = static::timerStopped()
				? static::$stoppedAt
				: microtime(true);

		return abs($end - static::$startedAt);
	}

	/**
	 * Get the elapsed time in string.
	 *
	 * @param bool $stop
	 * @return string
	 */
	public static function getElapsedTime($stop = true)
	{
		return sprintf("%.4f", static::getElapsedRaw($stop));
	}

}