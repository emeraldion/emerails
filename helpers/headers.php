<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2025
 *
 * @format
 */

/**
 *	@class Headers
 *	@short Helper object to set and parse headers.
 */
abstract class Headers
{
    const CACHE_CONTROL = 'Cache-Control';
    const CONTENT_ENCODING = 'Content-Encoding';
    const CONTENT_LENGTH = 'Content-Length';
    const CONTENT_TYPE = 'Content-Type';
    const DATE = 'Date';
    const EXPIRES = 'Expires';
    const LAST_MODIFIED = 'Last-Modified';
    const LOCATION = 'Location';
    const PRAGMA = 'Pragma';
    const REFERRER = 'Referer';
    const REFRESH = 'Refresh';

    public static function get(array $headers = [], string $name): ?string
    {
        if (array_key_exists($name, $headers)) {
            return $headers[$name];
        } elseif (array_key_exists($k = strtolower($name), $headers)) {
            return $headers[$k];
        } elseif (array_key_exists($k = mb_convert_case($name, MB_CASE_TITLE_SIMPLE), $headers)) {
            return $headers[$k];
        }
        return null;
    }
}
