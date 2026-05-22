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

/**
 *	@class Headers
 *	@short Helper object to set and parse headers.
 */
abstract class Headers
{
    const ACCEPT = 'Accept';
    const ACCEPT_ENCODING = 'Accept-Encoding';
    const ACCEPT_LANGUAGE = 'Accept-Language';
    const ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    const CACHE_CONTROL = 'Cache-Control';
    const CONNECTION = 'Connection';
    const CONTENT_ENCODING = 'Content-Encoding';
    const CONTENT_LENGTH = 'Content-Length';
    const CONTENT_TYPE = 'Content-Type';
    const COOKIE = 'Cookie';
    const DATE = 'Date';
    const DNT = 'Dnt';
    const EXPIRES = 'Expires';
    const HOST = 'Host';
    const LAST_MODIFIED = 'Last-Modified';
    const LOCATION = 'Location';
    const ORIGIN = 'Origin';
    const PRAGMA = 'Pragma';
    const REFERRER = 'Referer';
    const REFRESH = 'Refresh';
    const SEC_CH_UA = 'Sec-Ch-Ua';
    const SEC_CH_UA_MOBILE = 'Sec-Ch-Ua-Mobile';
    const SEC_CH_UA_PLATFORM = 'Sec-Ch-Ua-Platform';
    const SEC_FETCH_DEST = 'Sec-Fetch-Dest';
    const SEC_FETCH_MODE = 'Sec-Fetch-Mode';
    const SEC_FETCH_SITE = 'Sec-Fetch-Site';
    const SEC_FETCH_USER = 'Sec-Fetch-User';
    const UPGRADE_INSECURE_REQUESTS = 'Upgrade-Insecure-Requests';
    const USER_AGENT = 'User-Agent';

    public static function get(array $headers, string $name): ?string
    {
        $ret = null;
        if (array_key_exists($name, $headers)) {
            $ret = $headers[$name];
        } elseif (array_key_exists($k = strtolower($name), $headers)) {
            $ret = $headers[$k];
        } elseif (array_key_exists($k = mb_convert_case($name, MB_CASE_TITLE_SIMPLE), $headers)) {
            $ret = $headers[$k];
        }
        // Last one wins
        if (is_array($ret)) {
            $ret = last($ret);
        }
        return $ret;
    }
}
