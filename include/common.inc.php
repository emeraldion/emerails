<?php
	/**
	 *	Project EmeRails - Codename Ocarina
	 *
	 *	© 2008 Claudio Procida
	 *	http://www.emeraldion.it
	 *
	 */

	$irregular_nouns = array(
		'person' => 'people',
		'child' => 'children',
		'man' => 'men',
		'woman' => 'women',
		);

	function http_error($code)
	{
		header(sprintf("Location: http://%s/error/%s.html",
			$_SERVER['HTTP_HOST'],
			$code));
		exit();
	}

	function pluralize($term)
	{
		global $irregular_nouns;
		
		foreach ($irregular_nouns as $singular => $plural)
		{
			if (ends_with($term, $singular))
			{
				return substr($term, 0, strlen($term) - strlen($singular)) .
					$plural;
			}
		}
		if (array_key_exists($term, $irregular_nouns))
		{
			return $irregular_nouns[$term];
		}
		if (ends_with($term, 'child'))
		{
			// WARNING: NOT EXACTLY WHAT WANTED!!
			return str_replace('child', 'children', $term);
		}
		if (ends_with($term, 's') ||
			ends_with($term, 'x') ||
			ends_with($term, 'o'))
		{
			return $term . 'es';
		}
		if (ends_with($term, 'y'))
		{
			return substr($term, 0, strlen($term) - 1) . 'ies';
		}
		return $term . 's';
	}
	
	function singularize($term)
	{
		global $irregular_nouns;
		
		foreach ($irregular_nouns as $singular => $plural)
		{
			if (ends_with($term, $plural))
			{
				return substr($term, 0, strlen($term) - strlen($plural)) .
					$singular;
			}
		}
		if (in_array($term, $irregular_nouns))
		{
			return array_search($term, $irregular_nouns);
		}
		if (ends_with($term, 'xes') ||
			ends_with($term, 'oes'))
		{
			return substr($term, 0, strlen($term) - 2);
		}
		if (ends_with($term, 'ies'))
		{
			return substr($term, 0, strlen($term) - 3) . 'y';
		}
		if (ends_with($term, 's'))
		{
			return substr($term, 0, strlen($term) - 1);
		}
		return $term;
	}
	
	function ends_with($term, $suffix)
	{
		return strrpos($term, $suffix) === (strlen($term) - strlen($suffix));
	}
	
	function class_name_to_table_name($classname)
	{
		return pluralize(camel_case_to_joined_lower($classname));
	}
	
	function table_name_to_class_name($tablename)
	{
		return joined_lower_to_camel_case(singularize($tablename));
	}
	
	function joined_lower_to_camel_case($text)
	{
		return preg_replace('/(^[a-z])|_([a-z])/e', 'strtoupper(\'$1$2\')', $text);
	}
	
	function camel_case_to_joined_lower($text)
	{
		$text = preg_replace('/([A-Z])/', '_$1', $text);
		$text = preg_replace('/^_/', '', $text);
		return strtolower($text);
	}
	
	function class_name_to_foreign_key($classname)
	{
		$fkey = preg_replace('/([A-Z])/', '_$1', $classname);
		$fkey = preg_replace('/^_/', '', $fkey);
		$fkey = strtolower($fkey);
		$fkey .= "_id";
		return $fkey;
	}
	
	function table_name_to_foreign_key($tablename)
	{
		$fkey = singularize($tablename);
		$fkey .= "_id";
		return $fkey;
	}
	
	function l($str)
	{
		return localized($str);
	}
	
	function localized($str)
	{
		return Localization::localize($str);
	}
	
	function h($str)
	{
		return htmlentities($str);
	}
	
	function s($str)
	{
		return addslashes($str);
	}
	
	function limit_3($val, $a, $b)
	{
		$min = min($a, $b);
		$max = max($a, $b);
		if (is_numeric($val))
		{
			if ($min <= $val)
			{
				if ($val <= $max)
				{
					return $val;
				}
				return $max;
			}
			return $min;
		}
		return $min;
	}
?>