<?php
/**
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
