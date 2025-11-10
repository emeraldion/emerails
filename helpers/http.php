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

use Emeraldion\EmeRails\Config;

/**
 *	@class HTTP
 *	@short Helper class to manipulate HTTP error codes.
 */
class HTTP
{
    /**
     *	@fn error($code, $headers)
     *	@short Redirects the request to the error page for the desired HTTP error code.
     *	@details This method redirects the request to the ErrorController action method that
     *	handles the HTTP error code <tt>code</tt>, optionally sending a set of headers.
     *	@param code The HTTP error code.
     *	@param headers An optional set of HTTP headers to send to the client.
     */
    public static function error($code = 500, $headers = [])
    {
        if ($code < 400 || $code > 599) {
            throw new Exception(sprintf('Not an HTTP error response status code: %d', $code));
        }
        foreach ($headers as $header => $value) {
            header("$header: $value");
        }
        $_SESSION['error_processed'] = true;
        header(
            sprintf(
                'Location: %s://%s%serror/%s.html',
                isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http',
                $_SERVER['HTTP_HOST'],
                Config::get('APPLICATION_ROOT'),
                $code
            )
        );
        exit();
    }
}
