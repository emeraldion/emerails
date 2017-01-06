<?php
	/**
	 *	Project EmeRails - Codename Ocarina
	 *
	 *	Copyright (c) 2008, 2017 Claudio Procida
	 *	http://www.emeraldion.it
	 *
	 */
	
	require_once(dirname(__FILE__) . "/time.php");
	
	/**
	 *	@class Cookie
	 *	@short Helper object for cookie support.
	 */
	class Cookie
	{
		/**
		 *	@fn set($name, $value, $expires, $path, $secure)
		 *	@short Sets the value and other parameters of a cookie.
		 *	@details The value of the cookie is added to the superglobal <tt>_COOKIE</tt> array in order
		 *	to be readily available on the rest of the code.
		 *	@param name The name of the cookie.
		 *	@param value The value of the cookie.
		 *	@param path The path for which the cookie should be valid.
		 *	@param domain The domain for which the cookie should be valid.
		 *	@param secure Whether the cookie should be secure.
		 */
		public static function set($name, $value = '', $expires = 0, $path = '/', $domain = NULL, $secure = FALSE)
		{
			$_COOKIE[$name] = $value;
			setcookie($name, $value, $expires, $path, $domain, $secure);
		}
		
		/**
		 *	@fn get($name)
		 *	@short Gets the value of a cookie.
		 *	@param name The name of the cookie.
		 */
		public static function get($name)
		{
			if (isset($_COOKIE[$name]))
			{
				return $_COOKIE[$name];
			}
			return NULL;
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
			setcookie($name, '', Time::yesterday(), '/', NULL, FALSE);
		}
	}
?>