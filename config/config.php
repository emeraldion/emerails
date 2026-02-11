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

class Config
{
    private static $keys = [];

    public static function set($key, $value)
    {
        self::$keys[$key] = $value;
    }

    public static function get(string $key, $default_value = null)
    {
        if (isset(self::$keys[$key])) {
            return self::$keys[$key];
        }
        return $default_value;
    }

    protected static function reset()
    {
        self::$keys = [];
    }

    public static function dump()
    {
        print_r(self::$keys);
    }
}
