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

function QueryString_implode_item($item, $key, $pluralized_keys = [])
{
    if (in_array($key, $pluralized_keys) && !is_array($item)) {
        $item = [$item];
    }
    if (is_array($item)) {
        $ret = [];
        foreach ($item as $i) {
            $ret[] = urlencode($key . '[]') . QueryString::EQUALS . urlencode($i ?? '');
        }
        $item = implode(QueryString::SEPARATOR, $ret);
    } else {
        $item = urlencode($key) . QueryString::EQUALS . urlencode($item ?? '');
    }
    return $item;
}

function QueryString_explode_item($item)
{
    return array_map('urldecode', explode(QueryString::EQUALS, $item));
}

/**
 *	@class QueryString
 *	@short Helper class to manipulate query strings.
 */
class QueryString
{
    /**
     *  @const
     *  @short Query string separator, defaults to '&'
     */
    const SEPARATOR = '&';

    /**
     *  @const
     *  @short Key-value separator, defaults to '='
     */
    const EQUALS = '=';

    static $pluralized_keys = [];

    /**
     *	@fn from_assoc($parts)
     *	@short Writes the query string for an associative array
     *	@param parts The associative array
     */
    public static function from_assoc(array $parts): string
    {
        $ret = [];
        foreach ($parts as $key => $item) {
            $ret[$key] = QueryString_implode_item($item, $key, self::$pluralized_keys);
        }

        return implode(QueryString::SEPARATOR, $ret);
    }

    /**
     * @fn to_assoc($string)
     * @short Parses a query string into an associative array
     * @param string The query string
     */
    public static function to_assoc(string $string): array
    {
        $ret = [];
        if ($string && ($parts = explode(QueryString::SEPARATOR, $string))) {
            foreach ($parts as $part) {
                [$key, $value] = QueryString_explode_item($part);
                if (str_ends_with($key, '[]')) {
                    $key = substr($key, 0, -2);
                }
                if (array_key_exists($key, $ret)) {
                    if (!is_array($ret[$key])) {
                        $ret[$key] = [$ret[$key]];
                    }
                    $ret[$key][] = $value;
                } else {
                    $ret[$key] = $value;
                }
            }
        }
        return $ret;
    }

    /**
     * @fn replace($key, $val, $pluralized_keys)
     * @short Replaces a URL param in the query string with the requested value
     * @param key The key to replace
     * @param val The value to replace
     */
    public static function replace(string $key, $val)
    {
        $pos = strpos($_SERVER['REQUEST_URI'], '?');
        if ($pos != false) {
            $query_string = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?') + 1);
            $params = self::to_assoc($query_string);
        } else {
            $params = [];
        }
        $params[$key] = $val;
        return self::from_assoc($params);
    }

    /**
     * @fn replace($key, $val, $pluralized_keys)
     * @short Sets a list of URL param names to always keep plural
     * @detail Strings in the array passed as argument will be forcibly treated as multivalue URL
     * params when encountered by <tt>from_assoc(array $parts)</tt>, even when they have a single value.
     * @param pluralized_keys An array of strings to forcibly treat as multivalue URL params
     */
    public static function set_pluralized_keys(array $pluralized_keys): void
    {
        self::$pluralized_keys = $pluralized_keys;
    }
}
