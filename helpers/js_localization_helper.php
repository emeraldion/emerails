<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2024
 *
 * @format
 */

use Emeraldion\EmeRails\Config;

class JSLocalizationHelper
{
    /**
     *	@attr base_dir
     *	@short The base directory for localization files.
     *  @details Defaults to the parent directory of this file.
     */
    private static $base_dir = __DIR__ . '/../';

    /**
     *	@attr strings_table
     *	@short Table of translated strings.
     */
    private static $strings_table;

    private function __construct() {}

    /**
     *	@fn set_base_dir($base_dir)
     *	@short Initializes the base directory for localization files.
     *	@param base_dir The base directory. Defaults to the parent directory of this file.
     */
    public static function set_base_dir($base_dir = __DIR__ . '/../')
    {
        self::$base_dir = $base_dir;
    }

    public static function entries()
    {
        if (!self::$strings_table) {
            self::load_strings_table();
        }
        return json_encode(self::$strings_table);
    }

    /**
     *	@fn load_strings_file($lang)
     *	@short Loads the strings file.
     *	@param lang The language for the strings file.
     */
    private static function load_strings_file($lang = 'en')
    {
        $strings_file = sprintf('%sassets/strings/js/localizable-%s.strings', self::$base_dir, $lang);

        if (file_exists($strings_file)) {
            return file_get_contents($strings_file);
        } elseif ($lang != 'en') {
            return self::load_strings_file('en');
        }
        return 'array();';
    }

    /**
     *	@fn load_strings_table
     *	@short Loads the string table for the current language.
     *	@details Language is obtained by the request parameters.
     */
    private static function load_strings_table()
    {
        $table = [];

        $js_strings = self::load_strings_file(@$_COOKIE[Config::get('LANGUAGE_COOKIE')] /* GLOBAL */);
        $table = array_merge($table, eval("return {$js_strings}"));

        self::$strings_table = $table;
    }
}
