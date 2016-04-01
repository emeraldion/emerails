<?php

require_once(dirname(__FILE__) . '/../../../models/base.php');
require_once(dirname(__FILE__) . '/../../../include/' . DB_ADAPTER. '_adapter.php');

class TestModel extends ActiveRecord
{
}

class TestWidget extends ActiveRecord
{
}

class TestGroup extends ActiveRecord
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
    $instance_factory = new TestModel();
    $instances = $instance_factory->find_all();

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
    $instance_factory = new TestModel();
    $instances = $instance_factory->find_all(array(
      'where_clause' => "`name` = 'foo'",
    ));

    $this->assertNotNull($instances);
    $this->assertEquals(1, count($instances));

    $instance = $instances[0];
    $this->assertNotNull($instance);
    $this->assertEquals('foo', $instance->name);

    $instances = $instance_factory->find_all(array(
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
    $this->assertNotNull($instance->test_widget);
    $this->assertEquals('red', $instance->test_widget->color);

    $instance = $instance->find_all(array(
      'where_clause' => "`name` = 'foo'"
    ))[0];
    $instance->has_one('test_widgets');
    $this->assertNotNull($instance->test_widget);
    $this->assertEquals('red', $instance->test_widget->color);

    $instance = new TestModel();
    $instance->find_by_id(2);
    $instance->has_one('test_widgets');
    $this->assertNotNull($instance->test_widget);
    $this->assertEquals('blue', $instance->test_widget->color);

    $instance = $instance->find_all(array(
      'where_clause' => "`name` = 'bar'"
    ))[0];
    $instance->has_one('test_widgets');
    $this->assertNotNull($instance->test_widget);
    $this->assertEquals('blue', $instance->test_widget->color);
  }

  public function test_belongs_to()
  {
    $instance = new TestWidget();
    $instance->find_by_id(1);
    $instance->belongs_to('test_models');
    $this->assertNotNull($instance->test_model);
    $this->assertEquals('foo', $instance->test_model->name);

    $instance = $instance->find_all(array(
      'where_clause' => "`color` = 'red'"
    ))[0];
    $instance->belongs_to('test_models');
    $this->assertNotNull($instance->test_model);
    $this->assertEquals('foo', $instance->test_model->name);

    $instance = new TestWidget();
    $instance->find_by_id(2);
    $instance->belongs_to('test_models');
    $this->assertNotNull($instance->test_model);
    $this->assertEquals('bar', $instance->test_model->name);

    $instance = $instance->find_all(array(
      'where_clause' => "`color` = 'blue'"
    ))[0];
    $instance->belongs_to('test_models');
    $this->assertNotNull($instance->test_model);
    $this->assertEquals('bar', $instance->test_model->name);
  }

  public function test_has_and_belongs_to_many()
  {
    $instance = new TestModel();
    $instance->find_by_id(1);
    $instance->has_and_belongs_to_many('test_groups');
    $this->assertNotNull($instance->test_groups);
    $this->assertEquals(1, count($instance->test_groups));
    foreach ($instance->test_groups as $test_group)
    {
      $this->assertTrue(in_array($instance, $test_group->test_models));
    }

    $instance = new TestModel();
    $instance->find_by_id(2);
    $instance->has_and_belongs_to_many('test_groups');
    $this->assertNotNull($instance->test_groups);
    $this->assertEquals(2, count($instance->test_groups));
    foreach ($instance->test_groups as $test_group)
    {
      $this->assertTrue(in_array($instance, $test_group->test_models));
    }
  }
}
?>
