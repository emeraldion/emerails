<?php
/**
 * @format
 */

require_once __DIR__ . '/../../../helpers/request.php';

class MockRequest extends Request
{
    public static function purge_querystring_spy()
    {
        return self::purge_querystring();
    }
}

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function test_construct()
    {
        $request = new Request();
        $this->assertNotNull($request);
    }

    public function test_get_parameter()
    {
        $request = new Request();
        $_REQUEST['foo'] = 'bar';
        $this->assertEquals('bar', $request->get_parameter('foo'));
    }

    public function test_is_head()
    {
        $request = new Request();
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $this->assertTrue($request->is_head());
    }

    public function test_is_get()
    {
        $request = new Request();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue($request->is_get());
    }

    public function test_is_post()
    {
        $request = new Request();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue($request->is_post());
    }

    public function test_is_put()
    {
        $request = new Request();
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertTrue($request->is_put());
    }

    public function test_is_delete()
    {
        $request = new Request();
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertTrue($request->is_delete());
    }

    public function test_purge_querystring()
    {
        define('QS1', 'foo=bar&baz=1');

        $_SERVER['QUERY_STRING'] = QS1;
        $this->assertEquals(QS1, MockRequest::purge_querystring_spy());
    }
}
?>
