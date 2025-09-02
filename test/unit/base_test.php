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

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/db.conf.php';

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Db;
use Emeraldion\EmeRails\DbAdapters\MysqliAdapter;
use Emeraldion\EmeRails\DbAdapters\MysqlAdapter;

Db::register_adapter(new MysqliAdapter(), MysqliAdapter::NAME);
Db::register_adapter(new MysqlAdapter(), MysqlAdapter::NAME);

error_reporting(E_ALL | E_STRICT);

/**
 *	@class UnitTestBase
 *	@short Base class for Unit Testing.
 *	@details Unit testing allows you to run custom tests focused on validating the functionality
 *	of model objects. It is recommended that you create a test case for every method of the object.
 */
abstract class UnitTestBase extends \PHPUnit\Framework\TestCase
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

    /**
     * @short Asserts that a callback throws an error with the expected message
     * @detail Used to work around the appalling warning thrown by PHPUnit 9:
     *
     * Expecting E_ERROR and E_USER_ERROR is deprecated and will no longer be possible in PHPUnit 10.
     *
     * Inspired by https://blog.adamcameron.me/2023/02/ugh-phpunit-and-dealing-with.html
     * @param boolean $condition
     * @param string  $message
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public function assertErrorWithMessage(callable $callback, string $message)
    {
        try {
            $callback();
            $this->fail("Expected an error, didn't get one");
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->assertStringContainsString($message, $e->getMessage());
        }
    }

    protected function with_db_debug($fn)
    {
        try {
            Config::set('DB_DEBUG', true);
            $fn();
        } finally {
            Config::set('DB_DEBUG', false);
        }
    }
}
