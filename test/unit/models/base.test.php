<?php

require_once(dirname(__FILE__) . '/../../../models/base.php');
require_once(dirname(__FILE__) . '/../../../include/' . DB_ADAPTER. '_adapter.php');

class TestModel extends ActiveRecord
{
}

class TestWidget extends ActiveRecord
{
}

class ActiveRecordTest extends \PHPUnit_Framework_TestCase
{
  public function test_construct()
  {
    $instance = new TestModel();
    $this->assertNotNull($instance);
  }

  public function test_find_by_id()
  {
    $instance = new TestModel();
    $instance->find_by_id(1);
    $this->assertNotNull($instance);
    $this->assertEquals('foo', $instance->name);

    $instance = new TestModel();
    $instance->find_by_id(2);
    $this->assertNotNull($instance);
    $this->assertEquals('bar', $instance->name);
  }

  public function test_find_all_no_args()
  {
    $instances = TestModel::find_all();

    $this->assertNotNull($instances);
    $this->assertEquals(2, count($instances));

    $instance = $instances[0];
    $this->assertNotNull($instance);
    $this->assertEquals('foo', $instance->name);

    $instance = $instances[1];
    $this->assertNotNull($instance);
    $this->assertEquals('bar', $instance->name);
  }

  public function test_find_all_where_clause()
  {
    $instances = TestModel::find_all(array(
      'where_clause' => "`name` = 'foo'",
    ));

    $this->assertNotNull($instances);
    $this->assertEquals(1, count($instances));

    $instance = $instances[0];
    $this->assertNotNull($instance);
    $this->assertEquals('foo', $instance->name);

    $instances = TestModel::find_all(array(
      'where_clause' => "`name` = 'bar'",
    ));

    $instance = $instances[0];
    $this->assertNotNull($instance);
    $this->assertEquals('bar', $instance->name);
  }

  public function test_has_one()
  {
    $instance = new TestModel();
    $instance->find_by_id(1);
    $instance->has_one('test_widgets');
    $this->assertNotNull($instance->widget);
    $this->assertEquals('red', $instance->widget->color);

    $instance = new TestModel();
    $instance->find_by_id(2);
    $instance->has_one('test_widgets');
    $this->assertNotNull($instance->widget);
    $this->assertEquals('blue', $instance->widget->color);
  }
}
?>
