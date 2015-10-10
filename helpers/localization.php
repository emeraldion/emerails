<?php
	require_once(dirname(__FILE__) . "/../include/tag_support.inc.php");

	define("LOCALIZATION_DEBUG", 0);

	/**
	 *	@class Localization
	 *	@short Adds localization support to EmeRails.
	 */
	class Localization
	{
		/**
		 *	@attr languages
		 *	@short Array of supported languages.
		 */
		public static $languages = array('it', 'en', 'es');

		/**
		 *	@attr strings_table
		 *	@short Table of translated strings.
		 */
		private static $strings_table;
	
		/**
		 *	@fn localize($term)
		 *	@short Localizes a string according to the current language settings.
		 *	@param term The string to be translated.
		 */
		public static function localize($term)
		{
			if (!self::$strings_table)
			{
				self::load_strings_table();
			}
			return self::wrap((array_key_exists($term, self::$strings_table)) ?
				self::$strings_table[$term] : $term);
		}

		/**
		 *	@fn add_strings_table
		 *	@short Adds another strings table for a desired controller.
		 *	@details Language is obtained by the request parameters.
		 *	@param controller The name of the controller.
		 */
		public static function add_strings_table($controller)
		{
			$table = self::$strings_table;
		
			$local_strings = self::load_strings_file(@$_COOKIE['hl'], $controller);
			$table = array_merge(self::$strings_table, eval("return {$local_strings};"));
		
			self::$strings_table = $table;
		}
		
		/**
		 *	@fn wrap($term)
		 *	@short Wraps a string in a parent HTML element to add debug style selectors.
		 *	@param term The string to be wrapped.
		 */
		private static function wrap($term)
		{
			if (!LOCALIZATION_DEBUG) return $term;
			return span($term, array('class' => 'localization-debug'));
		}
	
		/**
		 *	@fn load_strings_file($lang, $controller)
		 *	@short Loads the string file for the desired controller and language.
		 *	@param lang The language for the strings file.
		 *	@param controller The name of the controller.
		 */
		private static function load_strings_file($lang = 'en', $controller = NULL)
		{
			$strings_file = $controller ?
				dirname(__FILE__) . "/../assets/strings/$controller/localizable-$lang.strings" :
				dirname(__FILE__) . "/../assets/strings/localizable-$lang.strings";
			
			//print_r($strings_file);
		
			if (file_exists($strings_file))
			{
				return file_get_contents($strings_file);
			}
			else if ($lang != 'en')
			{
				return self::load_strings_file('en', $controller);
			}
			return "array();";
		}

		/**
		 *	@fn load_strings_table
		 *	@short Loads the string table for the current controller and language.
		 *	@details Controller and language are obtained by the request parameters.
		 */
		private static function load_strings_table()
		{
			$table = array();
		
			$controller = $_REQUEST['controller'];
		
			$global_strings = self::load_strings_file(@$_COOKIE['hl'] /* GLOBAL */);
			$table = array_merge($table, eval("return {$global_strings}"));
			$local_strings = self::load_strings_file(@$_COOKIE['hl'], $controller);
			$table = array_merge($table, eval("return {$local_strings}"));
		
			self::$strings_table = $table;
		}
	}

?>
