<?php

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
	 * The format used by elapsed().
	 *
	 * @var string
	 */
	private static $format = "%.4f";

	/**
	 * Create a timer.
	 *
	 */
	public function __construct()
	{
		static::$instance = $this;
	}

	/**
	 * @param string $format
	 */
	public static function setFormat($format)
	{
		self::$format = $format;

		return static::$instance;
	}

	/**
	 * @return string
	 */
	public static function getFormat()
	{
		return self::$format;
	}

	/**
	 * Start a timer.
	 *
	 * @param string $timer
	 * @return Timer
	 */
	private function start($timer = 'default')
	{
		static::$startedAt[$timer] = microtime(true);

		static::$stoppedAt[$timer] = null;

		return static::$instance;
	}

	/**
	 * Put it to sleep a while.
	 *
	 * @param int $seconds
	 * @return Timer
	 */
	private function sleep($seconds = 1)
	{
		sleep($seconds);

		return static::$instance;
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

		return static::$instance;
	}

	/**
	 * Check if timer is started.
	 *
	 * @param string $timer
	 * @return bool
	 */
	private function isStarted($timer = 'default')
	{
		return !$this->isStopped($timer) && ! is_null(static::$startedAt[$timer]);
	}

	/**
	 * Check if timer is stopped.
	 *
	 * @param string $timer
	 * @return bool
	 */
	private function isStopped($timer = 'default')
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
	private function elapsedRaw($timer = 'default', $stop = true)
	{
		if ($stop)
		{
			static::stop($timer);
		}

		$end = static::isStopped($timer)
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
	private function elapsed($timer = 'default', $stop = true)
	{
		return sprintf($this->getFormat(), static::elapsedRaw($timer, $stop));
	}

	/**
	 * Provides static calls.
	 *
	 * @param $name
	 * @param array $arguments
	 * @return mixed
	 */
	public static function __callStatic($name, array $arguments)
	{
		if ( ! static::$instance)
		{
			$class = __CLASS__;

			static::$instance = new $class;
		}

		return call_user_func_array([static::$instance, $name], $arguments);
	}

	/**
	 * Provides dynamic calls.
	 *
	 * @param $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, array $arguments)
	{
		return call_user_func_array([$this, $name], $arguments);
	}
	
}
