<?php

/**
 *	@class UnitTest
 *	@short Base class for Unit Testing.
 *	@details Unit testing allows you to run custom tests focused on validating the functionality
 *	of model objects. It is recommended that you create a test case for every method of the object.
 */
abstract class UnitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @short Asserts that an expression is in the range of two values.
     * @param  boolean $condition
     * @param  string  $message
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public static function assertInRange($expr, $min, $max, $message = '')
    {
        $condition = $expr >= $min && $expr <= $max;
        self::assertThat($condition, self::isTrue(), $message);
    }
}

?>
