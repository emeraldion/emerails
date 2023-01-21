<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2023
 *
 * @format
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
