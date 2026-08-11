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

require_once __DIR__ . '/time.php';

/**
 *	@class Cookie
 *	@short Helper object for cookie support.
 */
class Cookie
{
    /**
     *	@fn set($name, $value, $expires, $path, $secure, $http_only)
     *	@short Sets the value and other parameters of a cookie.
     *	@details The value of the cookie is added to the superglobal <tt>_COOKIE</tt> array in order
     *	to be readily available on the rest of the code.
     *	@param name The name of the cookie.
     *	@param value The value of the cookie.
     *	@param path The path for which the cookie should be valid.
     *	@param domain The domain for which the cookie should be valid.
     *	@param secure Whether the cookie should be secure.
     *	@param http_only When true the cookie will be made accessible only through the HTTP protocol.
     */
    public static function set(
        $name,
        $value = '',
        $expires = 0,
        $path = '/',
        $domain = null,
        $secure = false,
        $http_only = false
    ) {
        $_COOKIE[$name] = $value;
        setcookie($name, $value, $expires, $path, $domain, $secure, $http_only);
    }

    /**
     *	@fn get($name)
     *	@short Gets the value of a cookie.
     *	@param name The name of the cookie.
     */
    public static function get($name)
    {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        return null;
    }

    /**
     *	@fn delete($name)
     *	@short Deletes a cookie.
     *	@details Sets a cookie to some date in the past: this may seem ridiculous, but is the only recommended
     *	way to cause the deletion of a cookie.
     *	@warning This function should be called only for cookies set with <tt>set</tt> using default values,
     *	otherwise they won't be deleted properly. In all other cases, <tt>set</tt> should be used.
     *	@param name The name of the cookie.
     */
    public static function delete($name)
    {
        setcookie($name, '', Time::yesterday(), '/', null, false);
    }
}
