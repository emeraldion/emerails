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
    const LOCATION = 'Location';
    const LAST_MODIFIED = 'Last-Modified';
    const PRAGMA = 'Pragma';
    const REFRESH = 'Refresh';
}
