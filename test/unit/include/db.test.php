<?php

require_once(dirname(__FILE__) . '/../../../include/db.inc.php');
require_once(dirname(__FILE__) . '/../../../include/db_adapter.php');

class TestAdapterBase implements DbAdapter
{
	public function connect() {}

	public function select_db($database_name) {}

	public function close() {}

	public function prepare($query) {}

	public function exec() {}

	public function insert_id() {}

	public function escape($value) {}

	public function result($pos = 0, $colname = NULL) {}

	public function num_rows() {}

	public function affected_rows() {}

	public function fetch_assoc() {}

	public function fetch_array() {}

	public function free_result() {}

	public function print_query() {}
}

class TestAdapter extends TestAdapterBase
{
	const NAME = "test";
}

class TestAdapterOther extends TestAdapterBase
{
	const NAME = "test_other";
}

class DBTest extends \PHPUnit_Framework_TestCase
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
}
?>
