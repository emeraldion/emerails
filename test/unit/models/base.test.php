<?php

require_once(dirname(__FILE__) . '/../../../models/base.php');
require_once(dirname(__FILE__) . '/../../../include/' . DB_ADAPTER. '_adapter.php');

function create_test_model($names)
{
  $conn = Db::get_connection();

  foreach($names as $name)
  {
    $conn->prepare("INSERT INTO `test_models` (`name`) VALUES ('{$conn->escape($name)}')");
    $conn->exec();
  }

  Db::close_connection($conn);
}

function delete_test_models($names)
{
  $conn = Db::get_connection();

  foreach($names as $name)
  {
    $conn->prepare("DELETE FROM `test_models` WHERE `name` = '{$conn->escape($name)}'");
    $conn->exec();
  }

  Db::close_connection($conn);
}

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
  /**
   * @before
   */
  function setup()
  {
  }

  /**
   * @after
   */
  function teardown()
  {
    delete_test_models(array(
        'blip',
        'baz',
      ));
  }

  public function test_construct()
  {
    $instance = new TestModel();
    $this->assertNotNull($instance);
  }

  public function test_save()
  {
    $instance = new TestModel(array(
      'name' => 'baz',
    ));
    $this->assertNotNull($instance);
    $ret = $instance->save();
    $this->assertTrue($ret);
    $this->assertNotNull($instance->id);

    $other_instance = new TestModel();
    $this->assertNotNull($other_instance);
    $ret = $other_instance->find_by_id($instance->id);
    $this->assertTrue($ret);

    $this->assertEquals('baz', $other_instance->name);
  }

  public function test_delete()
  {
    create_test_model(array(
      'blip',
    ));

    $instance = new TestModel();
    $this->assertNotNull($instance);
    $instances = $instance->find_all(array(
      'where_clause' => "`name`= 'blip'"
    ));
    $this->assertEquals(1, count($instances));
    $instances[0]->delete();

    $other_instance = new TestModel();
    $this->assertNotNull($other_instance);
    $other_instances = $other_instance->find_all(array(
      'where_clause' => "`name`= 'blip'"
    ));
    $this->assertEquals(0, count($other_instances));
  }

  public function test_find_by_id()
  {
    $instance = new TestModel();
    $this->assertNotNull($instance);
    $ret = $instance->find_by_id(1);
    $this->assertTrue($ret);
    $this->assertEquals('foo', $instance->name);

    $instance = new TestModel();
    $this->assertNotNull($instance);
    $ret = $instance->find_by_id(2);
    $this->assertTrue($ret);
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
    $ret = $instance->find_by_id(1);
    $this->assertTrue($ret);
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
    $ret = $instance->find_by_id(2);
    $this->assertTrue($ret);
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
    $ret = $instance->find_by_id(1);
    $this->assertTrue($ret);
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
    $ret = $instance->find_by_id(2);
    $this->assertTrue($ret);
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
    $ret = $instance->find_by_id(1);
    $this->assertTrue($ret);
    $instance->has_and_belongs_to_many('test_groups');
    $this->assertNotNull($instance->test_groups);
    $this->assertEquals(1, count($instance->test_groups));
    foreach ($instance->test_groups as $test_group)
    {
      $this->assertTrue(in_array($instance, $test_group->test_models));
    }

    $instance = new TestModel();
    $ret = $instance->find_by_id(2);
    $this->assertTrue($ret);
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
