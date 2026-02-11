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

require_once __DIR__ . '/../base_test.php';

use Emeraldion\EmeRails\Helpers\Headers;

/**
 *	@class HeadersTest
 *	@short Test case for Headers helper object.
 */
class HeadersUnitTest extends UnitTestBase
{
    /**
     *	@fn test_get
     *	@short Test method for get().
     */
    public function test_get()
    {
        $this->assertEquals('Foo', Headers::get(['Content-Type' => ['Foo']], Headers::CONTENT_TYPE));
        $this->assertEquals('Foo', Headers::get(['Content-Type' => 'Foo'], Headers::CONTENT_TYPE));

        $this->assertEquals('Bar', Headers::get(['Content-Type' => ['Foo', 'Bar']], Headers::CONTENT_TYPE));

        $this->assertNull(Headers::get(['Content-Length' => '1234'], Headers::CONTENT_TYPE));
    }
}
