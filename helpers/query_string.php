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

function QueryString_implode_item(&$item, $key)
{
    $item = urlencode($key) . QueryString::EQUALS . urlencode($item);
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

    /**
     *	@fn from_assoc($parts)
     *	@short Writes the query string for an associative array
     *	@param parts The associative array
     */
    public static function from_assoc($parts)
    {
        array_walk($parts, 'QueryString_implode_item');

        return implode(QueryString::SEPARATOR, $parts);
    }

    /**
     *	@fn to_assoc($string)
     *	@short Parses a query string into an associative array
     *	@param string The query string
     */
    public static function to_assoc($string)
    {
        $ret = [];
        if ($string && ($parts = explode(QueryString::SEPARATOR, $string))) {
            foreach ($parts as $part) {
                $p = QueryString_explode_item($part);
                $ret[$p[0]] = $p[1];
            }
        }
        return $ret;
    }

    public static function replace($key, $val)
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
}
