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
	}
?>
