<?php
	/**
	 *	Project EmeRails - Codename Ocarina
	 *
	 *	Copyright (c) 2008, 2015 Claudio Procida
	 *	http://www.emeraldion.it
	 *
	 */

	/**
	 *	@class ReadingTime
	 *	@short Helper class to calculate reading time of articles.
	 */
	class ReadingTime
	{
		/**
		 *  @const
		 *  @short Number of words an adult can read in a minute
		 */
		const WORDS_PER_MIN = 300;

		/**
		 *	@fn minutes_for($string)
		 *	@short Calculates the minutes needed to read the given text
		 *	@param string The text to read
		 */
		public static function minutes_for($text)
		{
			return floor(str_word_count(strip_tags($text)) / self::WORDS_PER_MIN);
		}
	}

?>
