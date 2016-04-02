<?php
	require_once(dirname(__FILE__) . "/../../../helpers/query_string.php");
	require_once(dirname(__FILE__) . "/../base_test.php");

	/**
	 *	@class QueryStringUnitTest
	 *	@short Test case for QueryString helper object.
	 */
	class QueryStringUnitTest extends UnitTest
	{
		/**
		 *	@fn test_from_assoc
		 *	@short Test method for from_assoc().
		 */
		public function test_from_assoc()
		{
			$this->assertEquals('a=1', QueryString::from_assoc(array('a' => 1)), 'Unable to create query string');
      $this->assertEquals('a=1&b=2', QueryString::from_assoc(array('a' => 1, 'b' => 2)), 'Unable to create query string');
		}

		/**
		 *	@fn test_to_assoc
		 *	@short Test method for to_assoc().
		 */
		public function test_to_assoc()
		{
			$this->assertEquals(array('a' => '1'), QueryString::to_assoc('a=1'), 'Unable to parse query string');
			$this->assertEquals(array('a' => '1', 'b' => '2'), QueryString::to_assoc('a=1&b=2'), 'Unable to parse query string');
		}

		/**
		 *	@fn test_replace
		 *	@short Test method for replace().
		 */
		public function test_replace()
		{
			$_SERVER['REQUEST_URI'] = 'index.html?foo=1&bar=2';
			$this->assertEquals('foo=9&bar=2', QueryString::replace('foo', 9), 'Unable to replace query string');
			$this->assertEquals('foo=1&bar=a', QueryString::replace('bar', 'a'), 'Unable to replace query string');
			$this->assertEquals('foo=1&bar=2&baz=3', QueryString::replace('baz', 3), 'Unable to replace query string');

			$_SERVER['REQUEST_URI'] = 'index.html?foo=1&bar=2&baz=blip';
			$this->assertEquals('foo=9&bar=2&baz=blip', QueryString::replace('foo', 9), 'Unable to replace query string');
			$this->assertEquals('foo=1&bar=a&baz=blip', QueryString::replace('bar', 'a'), 'Unable to replace query string');
			$this->assertEquals('foo=1&bar=2&baz=3', QueryString::replace('baz', 3), 'Unable to replace query string');

			$_SERVER['REQUEST_URI'] = 'index.html';
			$this->assertEquals('foo=9', QueryString::replace('foo', 9), 'Unable to replace query string');
			$this->assertEquals('bar=a', QueryString::replace('bar', 'a'), 'Unable to replace query string');
			$this->assertEquals('baz=3', QueryString::replace('baz', 3), 'Unable to replace query string');
		}
	}
?>
