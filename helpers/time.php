<?php
   /**
    *      Project EmeRails - Codename Ocarina
    *
    *      Copyright (c) 2008, 2017 Claudio Procida
    *      http://www.emeraldion.it
    *
    */

	/**
	 *	@class Time
	 *	@short Helper object for time calculations.
	 */
	class Time
	{
		/**
		 *	@fn tomorrow
		 *	@short Returs the number of seconds elapsed from the Unix Epoch to tomorrow.
		 */
		public static function tomorrow()
		{
			return time() + 24 * 3600;
		}

		/**
		 *	@fn yesterday
		 *	@short Returs the number of seconds elapsed from the Unix Epoch to yesterday.
		 */
		public static function yesterday()
		{
			return time() - 24 * 3600;
		}
		
		/**
		 *	@fn next_month
		 *	@short Returs the number of seconds elapsed from the Unix Epoch to next month.
		 */
		public static function next_month()
		{
			$now = time();
			return mktime(date('H', $now),
				date('i', $now),
				date('s', $now),
				date('m', $now) + 1,
				date('d', $now),
				date('Y', $now));
		}

		/**
		 *	@fn next_year
		 *	@short Returs the number of seconds elapsed from the Unix Epoch to next year.
		 */
		public static function next_year()
		{
			$now = time();
			return mktime(date('H', $now),
				date('i', $now),
				date('s', $now),
				date('m', $now),
				date('d', $now),
				date('Y', $now) + 1);
		}

		/**
		 *	@fn ago($time_amount)
		 *	@short Returs the number of seconds elapsed from the Unix Epoch to a given date.
		 *	@param time_amount A quantity of time (e.g. day).
		 */
		public static function ago($time_amount = NULL)
		{
			switch ($time_amount)
			{
				case 'hour':
					$time = time() - 3600;
					break;
				case 'day':
					$time = time() - 3600 * 24;
					break;
				case 'week':
					$time = time() - 3600 * 24 * 7;
					break;
				case 'month':
					$time = time() - 3600 * 24 * 30;
					break;
				default:
					$time = time();
			}
			return $time;
		}
	}

?>
