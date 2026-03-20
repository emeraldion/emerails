<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2026
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
    const GLOBAL_TABLE = '__GLOBAL__';

    /**
     * @attr base_dir
     * @short The base directory for localization files.
     * @details Defaults to the parent directory of this file.
     */
    private static $base_dir = __DIR__ . '/../';

    /**
     * @attr languages
     * @short Array of supported languages.
     */
    public static $languages = [
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
    ];

    /**
     * @attr strings
     * @short Table of localized strings.
     */
    private static $strings = [];

    /**
     * @attr tables
     * @short List of string tables.
     */
    private static $tables = [self::GLOBAL_TABLE];

    /**
     * @attr is_loaded
     * @short Have strings been loaded from tables yet?
     */
    private static $is_loaded = false;

    /**
     * @fn set_base_dir($base_dir)
     * @short Initializes the base directory for localization files.
     * @param base_dir The base directory. Defaults to the parent directory of this file.
     */
    public static function set_base_dir(string $base_dir = __DIR__ . '/../'): void
    {
        self::$base_dir = $base_dir;
    }

    /**
     * @fn reset()
     * @short Resets the localization helper.
     */
    public static function reset(): void
    {
        self::$is_loaded = false;
        self::$strings = [];
        self::$tables = [self::GLOBAL_TABLE];
    }

    /**
     * @fn localize($key)
     * @short Returns a localized string according to the current language settings.
     * @details This method tries to resolve the key in the localized strings table for
     * the current language settings. If the string is not found, the <tt>$fallback</tt>
     * will be returned. This is useful during development and for string extraction using
     * the <tt>emerails_localize</tt> command. If no fallback is provided, the key will be
     * returned as-is. Note that when the config setting <tt>LOCALIZATION_DEBUG</tt> is set
     * to a truthy value, this method will return strings wrapped for better visual
     * identification and troubleshooting.
     * @param key The key of the strings table.
     * @param fallback The fallback string to return when the key can't be resolved.
     * @see wrap($term)
     */
    public static function localize(string $key, ?string $fallback = null): string
    {
        if (!self::$is_loaded) {
            self::load_strings();
        }
        return self::wrap(array_key_exists($key, self::$strings) ? self::$strings[$key] : $fallback ?? $key);
    }

    /**
     * @fn add_strings
     * @short Adds a strings table to the localization.
     * @details Strings are lazy loaded when the first localized string is requested
     * @param table The name of the table.
     */
    public static function add_strings_table(string $table): void
    {
        if (!in_array($table, self::$tables)) {
            self::$tables[] = $table;
        }

        // If tables were already loaded, load the new strings table immediately
        if (self::$is_loaded) {
            $strings_table = self::load_strings_table($table, @$_COOKIE[Config::get('LANGUAGE_COOKIE')]);
            $strings = array_merge(self::$strings, eval("return {$strings_table}"));

            self::$strings = $strings;
        }
    }

    /**
     * @fn wrap($term)
     * @short Wraps a string in a parent HTML element to add debug style selectors.
     * @param term The string to be wrapped.
     */
    private static function wrap(string $term): string
    {
        if (Config::get('LOCALIZATION_DEBUG')) {
            return span($term, ['class' => 'localization-debug']);
        }
        return $term;
    }

    /**
     * @fn load_strings_table($table, $lang)
     * @short Loads the strings from the desired table and language.
     * @param table The name of the table.
     * @param lang The language for the strings file.
     */
    private static function load_strings_table(string $table, string $lang = 'en'): string
    {
        $strings_file =
            $table === self::GLOBAL_TABLE
                ? sprintf('%sassets/strings/localizable-%s.strings', self::$base_dir, $lang)
                : sprintf('%sassets/strings/%s/localizable-%s.strings', self::$base_dir, $table, $lang);

        if (file_exists($strings_file)) {
            return file_get_contents($strings_file);
        } elseif ($lang != 'en') {
            // Fall back to English
            return self::load_strings_file('en', $table);
        }
        return '[];';
    }

    /**
     * @fn load_strings
     * @short Loads the strings from the requested string tables and language.
     * @details Language is obtained from request parameters.
     */
    private static function load_strings(): void
    {
        if (self::$is_loaded) {
            return;
        }

        $strings = [];

        foreach (self::$tables as $table) {
            $strings_table = self::load_strings_table($table, @$_COOKIE[Config::get('LANGUAGE_COOKIE')]);
            $strings = array_merge($strings, eval("return {$strings_table}"));
        }

        self::$strings = $strings;
    }
}
