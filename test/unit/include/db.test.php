<?php
/**
 * @format
 */

use Emeraldion\EmeRails\Db;
use Emeraldion\EmeRails\DbAdapter;

class TestAdapterBase implements DbAdapter
{
    public function connect()
    {
    }

    public function select_db($database_name)
    {
    }

    public function close()
    {
    }

    public function prepare($query)
    {
    }

    public function exec()
    {
    }

    public function insert_id()
    {
    }

    public function exec_multiline()
    {
    }

    public function escape($value)
    {
    }

    public function result($pos = 0, $colname = null)
    {
    }

    public function num_rows()
    {
    }

    public function affected_rows()
    {
    }

    public function fetch_assoc()
    {
    }

    public function fetch_array()
    {
    }

    public function free_result()
    {
    }

    public function print_query()
    {
    }
}

class TestAdapter extends TestAdapterBase
{
    const NAME = 'test';
}

class TestAdapterOther extends TestAdapterBase
{
    const NAME = 'test_other';
}

class DBTest extends \PHPUnit\Framework\TestCase
{
    public function test_get_adapter()
    {
        $test_adapter = new TestAdapter();

        Db::register_adapter($test_adapter, TestAdapter::NAME);

        $this->assertNotNull(Db::get_adapter(TestAdapter::NAME));
        $this->assertEquals($test_adapter, Db::get_adapter(TestAdapter::NAME));
    }

    public function test_register_adapter()
    {
        $this->assertNull(Db::get_adapter(TestAdapterOther::NAME));

        $test_adapter_other = new TestAdapterOther();

        Db::register_adapter($test_adapter_other, TestAdapterOther::NAME);

        $this->assertNotNull(Db::get_adapter(TestAdapterOther::NAME));
        $this->assertEquals($test_adapter_other, Db::get_adapter(TestAdapterOther::NAME));
    }

    public function test_get_connection()
    {
        $test_adapter = new TestAdapter();

        Db::register_adapter($test_adapter, TestAdapter::NAME);

        // Two connections opened sequentially...
        $conn1 = Db::get_connection(TestAdapter::NAME);
        $conn2 = Db::get_connection(TestAdapter::NAME);

        // ...are different
        $this->assertFalse($conn1 === $conn2);

        // Closing the second connection
        Db::close_connection($conn2, TestAdapter::NAME);

        // The third connection is reused from the one just closed...
        $conn3 = Db::get_connection(TestAdapter::NAME);

        // ...so it differs from the first...
        $this->assertFalse($conn1 === $conn3);
        // ...but it's identical to the second
        $this->assertTrue($conn2 === $conn3);
    }
}
?>
