<?php

class Util
{
	/**
	 * Get the current time
	 *
	 * @return double
	 */
	public static function getStartTime()
	{
		$startTime = microtime(true);
		return $startTime;
	}

	/**
	 * Get the process time since $startTime
	 *
	 * @param double $startTime
	 * @return double
	 */
	public static function calculateProcessTime($startTime)
	{
		$endTime = microtime(true);
		$time = $endTime - $startTime;
		return $time;
	}

	/**
	 * Get the display for $time
	 *
	 * @param double $time
	 *
	 * @return string
	 */
	public static function timeForDisplay($time)
	{
		if ($time < 60)
		{
			return number_format($time, 2) . ' seconds';
		}
		$time = $time / 60;
		if ($time < 60)
		{
			return number_format($time, 2) . ' minutes';
		}
		$time = $time / 60;
		if ($time < 60)
		{
			return number_format($time, 2) . ' hours';
		}

		return number_format($time, 2) . " [no scale]";
	}

	/**
	 * Get a memory representation to display
	 *
	 * @return string
	 */
	public static function getMemoryDisplay($mem = -1)
	{
		if ($mem == - 1)
		{
			$mem = memory_get_usage();
		}

		if ($mem < 1024)
		{
			return number_format($mem, 2) . " bytes";
		}
		$mem = $mem / 1024;
		if ($mem < 1024)
		{
			return number_format($mem, 2) . " kb";
		}
		$mem = $mem / 1024;
		if ($mem < 1024)
		{
			return number_format($mem, 2) . " mb";
		}
		$mem = $mem / 1024;
		if ($mem < 1024)
		{
			return number_format($mem, 2) . " gb";
		}

		return number_format($mem, 2) . " [no scale]";
	}
}
?>