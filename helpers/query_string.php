<?php
	/**
	 *	Project EmeRails - Codename Ocarina
	 *
	 *	Copyright (c) 2008, 2015 Claudio Procida
	 *	http://www.emeraldion.it
	 *
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
		public static function from_assoc($parts) {
      array_walk($parts, 'QueryString_implode_item');

      return implode(QueryString::SEPARATOR, $parts);
		}

		/**
		 *	@fn to_assoc($string)
		 *	@short Parses a query string into an associative array
		 *	@param string The query string
		 */
		public static function to_assoc($string) {
			$parts = explode(QueryString::SEPARATOR, $string);
			$ret = array();

			foreach ($parts as $part) {
				$p = QueryString_explode_item($part);
				$ret[$p[0]] = $p[1];
			}

			return $ret;
		}

		public static function replace($key, $val)
		{
			$pos = strpos($_SERVER['REQUEST_URI'], '?');
			if ($pos != FALSE)
			{
				$query_string = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?') + 1);
				$params = self::to_assoc($query_string);
			}
			else
			{
				$params = array();
			}
			$params[$key] = $val;
			return self::from_assoc($params);
		}

	}

?>
