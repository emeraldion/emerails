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

require_once __DIR__ . '/../include/tag_support.inc.php';

use Emeraldion\EmeRails\Config;

/**
 *	@class Localization
 *	@short Adds localization support to EmeRails.
 */
class Localization
{
    /**
     *	@attr base_dir
     *	@short The base directory for localization files.
     *  @details Defaults to the parent directory of this file.
     */
    private static $base_dir = __DIR__ . '/../';

    /**
     *	@attr languages
     *	@short Array of supported languages.
     */
    public static $languages = array(
        'it',
        'en',
        'es',
        'fr',
        'de',
        'ja',
        'da',
        'ru',
        'pt',
        'pl',
        'cs',
        'nl',
        'fi',
        'sv',
        'el',
        'ro',
        'nb',
        'hu',
        'hr',
        'ko'
    );

    /**
     *	@attr strings_table
     *	@short Table of translated strings.
     */
    private static $strings_table;

    /**
     *	@fn set_base_dir($base_dir)
     *	@short Initializes the base directory for localization files.
     *	@param base_dir The base directory. Defaults to the parent directory of this file.
     */
    public static function set_base_dir($base_dir = __DIR__ . '/../')
    {
        self::$base_dir = $base_dir;
    }

    /**
     *	@fn localize($term)
     *	@short Localizes a string according to the current language settings.
     *	@param term The string to be translated.
     */
    public static function localize($term)
    {
        if (!self::$strings_table) {
            self::load_strings_table();
        }
        return self::wrap(array_key_exists($term, self::$strings_table) ? self::$strings_table[$term] : $term);
    }

    /**
     *	@fn add_strings_table
     *	@short Adds another strings table for a desired controller.
     *	@details Language is obtained by the request parameters.
     *	@param controller The name of the controller.
     */
    public static function add_strings_table($controller)
    {
        $table = self::$strings_table;

        $local_strings = self::load_strings_file(@$_COOKIE['hl'], $controller);
        $table = array_merge(self::$strings_table ?? array(), eval("return {$local_strings};"));

        self::$strings_table = $table;
    }

    /**
     *	@fn wrap($term)
     *	@short Wraps a string in a parent HTML element to add debug style selectors.
     *	@param term The string to be wrapped.
     */
    private static function wrap($term)
    {
        if (!Config::get('LOCALIZATION_DEBUG')) {
            return $term;
        }
        return span($term, array('class' => 'localization-debug'));
    }

    /**
     *	@fn load_strings_file($lang, $controller)
     *	@short Loads the string file for the desired controller and language.
     *	@param lang The language for the strings file.
     *	@param controller The name of the controller.
     */
    private static function load_strings_file($lang = 'en', $controller = null)
    {
        $strings_file = $controller
            ? sprintf('%sassets/strings/%s/localizable-%s.strings', self::$base_dir, $controller, $lang)
            : sprintf('%sassets/strings/localizable-%s.strings', self::$base_dir, $lang);

        // print_r($strings_file);

        if (file_exists($strings_file)) {
            return file_get_contents($strings_file);
        } elseif ($lang != 'en') {
            return self::load_strings_file('en', $controller);
        }
        return 'array();';
    }

    /**
     *	@fn load_strings_table
     *	@short Loads the string table for the current controller and language.
     *	@details Controller and language are obtained by the request parameters.
     */
    private static function load_strings_table()
    {
        $table = array();

        $global_strings = self::load_strings_file(@$_COOKIE['hl'] /* GLOBAL */);
        $table = array_merge($table, eval("return {$global_strings}"));
        if (isset($_REQUEST['controller'])) {
            $controller = $_REQUEST['controller'];
            $local_strings = self::load_strings_file(@$_COOKIE['hl'], $controller);
            $table = array_merge($table, eval("return {$local_strings}"));
        }

        self::$strings_table = $table;
    }
}

?>
