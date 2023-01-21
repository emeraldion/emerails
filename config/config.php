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

class Config
{
    private static $keys = array();

    public static function set($key, $value)
    {
        self::$keys[$key] = $value;
    }

    public static function get($key)
    {
        if (isset(self::$keys[$key])) {
            return self::$keys[$key];
        }
        return null;
    }

    protected static function reset()
    {
        self::$keys = array();
    }

    public static function dump()
    {
        print_r(self::$keys);
    }
}

?>
