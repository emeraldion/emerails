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

use Emeraldion\EmeRails\Helpers\QueryString;

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
        $this->assertEquals('a=1', QueryString::from_assoc(['a' => 1]), 'Unable to create query string');
        $this->assertEquals('a=1&b=2', QueryString::from_assoc(['a' => 1, 'b' => 2]), 'Unable to create query string');
    }

    /**
     *	@fn test_from_assoc_multivalue
     *	@short Test method for from_assoc() with multivalue URL params.
     */
    public function test_from_assoc_multivalue()
    {
        $this->assertEquals(
            'a%5B%5D=1&a%5B%5D=2',
            QueryString::from_assoc(['a' => [1, 2]]),
            'Unable to create query string'
        );
        $this->assertEquals(
            'a%5B%5D=1&a%5B%5D=2&b%5B%5D=3&b%5B%5D=4',
            QueryString::from_assoc(['a' => [1, 2], 'b' => [3, 4]]),
            'Unable to create query string'
        );
    }

    /**
     *	@fn test_from_assoc_mixedvalue
     *	@short Test method for from_assoc() with mixed single-value and multivalue URL params.
     */
    public function test_from_assoc_mixedvalue()
    {
        $this->assertEquals(
            'a=1&b%5B%5D=2&b%5B%5D=3',
            QueryString::from_assoc(['a' => 1, 'b' => [2, 3]]),
            'Unable to create query string'
        );
        $this->assertEquals(
            'a%5B%5D=1&a%5B%5D=2&b=3',
            QueryString::from_assoc(['a' => [1, 2], 'b' => 3]),
            'Unable to create query string'
        );
    }

    /**
     *	@fn test_to_assoc
     *	@short Test method for to_assoc().
     */
    public function test_to_assoc()
    {
        $this->assertEquals(['a' => '1'], QueryString::to_assoc('a=1'), 'Unable to parse query string');
        $this->assertEquals(['a' => '1', 'b' => '2'], QueryString::to_assoc('a=1&b=2'), 'Unable to parse query string');
    }

    /**
     *	@fn test_to_assoc_multivalue
     *	@short Test method for to_assoc() with multivalue URL params.
     */
    public function test_to_assoc_multivalue()
    {
        $this->assertEquals(
            ['a' => ['1', '2']],
            QueryString::to_assoc('a%5B%5D=1&a%5B%5D=2'),
            'Unable to parse query string'
        );
        $this->assertEquals(
            ['a' => ['1', '2'], 'b' => ['3', '4']],
            QueryString::to_assoc('a%5B%5D=1&a%5B%5D=2&b%5B%5D=3&b%5B%5D=4'),
            'Unable to parse query string'
        );
    }

    /**
     *	@fn test_to_assoc_mixedvalue
     *	@short Test method for to_assoc()  with mixed single-value and multivalue URL params.
     */
    public function test_to_assoc_mixedvalue()
    {
        $this->assertEquals(
            ['a' => ['1', '2'], 'b' => '1'],
            QueryString::to_assoc('a%5B%5D=1&a%5B%5D=2&b=1'),
            'Unable to parse query string'
        );
        $this->assertEquals(
            ['a' => '1', 'b' => ['2', '3', '4']],
            QueryString::to_assoc('a=1&b%5B%5D=2&b%5B%5D=3&b%5B%5D=4'),
            'Unable to parse query string'
        );
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

    /**
     *	@fn test_replace_multivalue
     *	@short Test method for replace() with multivalue URL params.
     */
    public function test_replace_multivalue()
    {
        // Replace single value with multivalue
        $_SERVER['REQUEST_URI'] = 'index.html?foo=1&bar=2';
        $this->assertEquals(
            'foo%5B%5D=8&foo%5B%5D=9&bar=2',
            QueryString::replace('foo', [8, 9]),
            'Unable to replace query string'
        );

        // Replace multivalue with single value
        $_SERVER['REQUEST_URI'] = 'index.html?foo%5B%5D=1&foo%5B%5D=2&bar=3';
        $this->assertEquals('foo=4&bar=3', QueryString::replace('foo', 4), 'Unable to replace query string');

        // Replace multivalue with multivalue
        $_SERVER['REQUEST_URI'] = 'index.html?foo%5B%5D=1&foo%5B%5D=2&bar=3';
        $this->assertEquals(
            'foo%5B%5D=4&foo%5B%5D=5&foo%5B%5D=6&bar=3',
            QueryString::replace('foo', [4, 5, 6]),
            'Unable to replace query string'
        );
    }
}
