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

require_once __DIR__ . '/../base_test.php';

use Emeraldion\EmeRails\Config;

/**
 *	@class ConfigUnitTest
 *	@short Test case for Config storage.
 */
class ConfigUnitTest extends UnitTest
{
    /**
     *	@fn test_set
     *	@short Test method for set().
     */
    public function test_set()
    {
        Config::set('foo.bar', 'baz');
        Config::set('foo.baz', 'bing');

        $this->assertEquals('baz', Config::get('foo.bar'));
        $this->assertEquals('bing', Config::get('foo.baz'));

        $this->assertNull(Config::get('anything.else'));
    }
}
?>
