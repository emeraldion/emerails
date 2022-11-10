<?php
/**
 * @format
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/db.conf.php';

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Db;
use Emeraldion\EmeRails\DbAdapters\MysqlAdapter;
use Emeraldion\EmeRails\DbAdapters\MysqliAdapter;
use Emeraldion\EmeRails\Models\ActiveRecord;

Db::register_adapter(new MysqlAdapter(), MysqlAdapter::NAME);
Db::register_adapter(new MysqliAdapter(), MysqliAdapter::NAME);

function create_test_model($names)
{
    $conn = Db::get_connection();

    foreach ($names as $name) {
        $conn->prepare("INSERT INTO `test_models` (`name`) VALUES ('{$conn->escape($name)}')");
        $conn->exec();
    }

    Db::close_connection($conn);
}

function delete_test_models($names)
{
    $conn = Db::get_connection();

    foreach ($names as $name) {
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

class TestVersion extends ActiveRecord
{
}

class ActiveRecordTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @before
     */
    function setUp(): void
    {
    }

    /**
     * @after
     */
    function teardown(): void
    {
        delete_test_models(array('blip', 'baz'));
    }

    public function test_construct()
    {
        $instance = new TestModel();
        $this->assertNotNull($instance);
    }

    public function test_save()
    {
        $instance = new TestModel(array(
            'name' => 'baz'
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

    public function test_save_sets_id()
    {
        $instance = new TestModel(array(
            'name' => 'test_save_sets_id'
        ));
        $this->assertNotNull($instance);
        $ret = $instance->save();
        $this->assertTrue($ret);
        $this->assertNotNull($instance->id);

        $conn = Db::get_connection();

        $conn->prepare("SELECT * FROM `test_models` WHERE `name` = 'test_save_sets_id'");
        $conn->exec();

        $result = $conn->fetch_assoc();

        $this->assertEquals('test_save_sets_id', $result['name']);
        $this->assertEquals($instance->id, $result['id']);

        $instance->delete();
    }

    public function test_save_dupe()
    {
        $instance = new TestModel(array(
            'name' => 'baz'
        ));
        $this->assertNotNull($instance, 'Model was not instantiated');
        $ret = $instance->save();
        $this->assertTrue($ret, 'Model was not saved');
        $this->assertNotNull($instance->id, 'Model id was not set');

        $other_instance = new TestModel(array(
            'id' => $instance->id,
            'name' => 'baz'
        ));
        $this->assertNotNull($other_instance, 'Duplicate model was not instantiated');
        $other_instance->_force_create = true;
        $other_instance->_ignore = true;
        $ret = $other_instance->save();
        $this->assertFalse($ret, 'Duplicate model was saved and not rejected');
    }

    public function test_delete()
    {
        create_test_model(array('blip'));

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
        $this->assertNull($other_instances);
    }

    public function test_static_find_by_id()
    {
        $instance = TestModel::find(1, 'TestModel');
        $this->assertNotNull($instance);
        $this->assertEquals('foo', $instance->name);

        $instance = TestModel::find(2, 'TestModel');
        $this->assertNotNull($instance);
        $this->assertEquals('bar', $instance->name);
    }

    public function test_static_find_by_id_no_match()
    {
        $instance = TestModel::find(3, 'TestModel');
        $this->assertNull($instance);
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
            'where_clause' => "`name` = 'foo'"
        ));

        $this->assertNotNull($instances);
        $this->assertEquals(1, count($instances));

        $instance = $instances[0];
        $this->assertNotNull($instance);
        $this->assertEquals('foo', $instance->name);

        $instances = $instance_factory->find_all(array(
            'where_clause' => "`name` = 'bar'"
        ));

        $this->assertNotNull($instances);
        $this->assertEquals(1, count($instances));

        $instance = $instances[0];
        $this->assertNotNull($instance);
        $this->assertEquals('bar', $instance->name);
    }

    public function test_count_all_no_args()
    {
        $instance_factory = new TestModel();
        $count = $instance_factory->count_all();

        $this->assertEquals(2, $count);
    }

    public function test_count_all_where_clause()
    {
        $instance_factory = new TestModel();
        $count = $instance_factory->count_all(array(
            'where_clause' => "`name` = 'foo'"
        ));

        $this->assertEquals(1, $count);

        $count = $instance_factory->count_all(array(
            'where_clause' => "`name` = 'bar'"
        ));

        $this->assertEquals(1, $count);
    }

    public function test_find_by_query()
    {
        $instance_factory = new TestModel();
        $instances = $instance_factory->find_by_query("SELECT * FROM `test_models` WHERE `name` = 'foo'");

        $this->assertNotNull($instances);
        $this->assertEquals(1, count($instances));

        $instance = $instances[0];
        $this->assertNotNull($instance);
        $this->assertEquals('foo', $instance->name);

        $instances = $instance_factory->find_by_query("SELECT * FROM `test_models` WHERE `name` = 'bar'");

        $this->assertNotNull($instances);
        $this->assertEquals(1, count($instances));

        $instance = $instances[0];
        $this->assertNotNull($instance);
        $this->assertEquals('bar', $instance->name);
    }

    public function test_has_one()
    {
        $instance = new TestModel();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $instance->has_one('test_widgets');
        $this->assertTrue($ret);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('red', $instance->test_widget->color);

        $instance = $instance->find_all(array(
            'where_clause' => "`name` = 'foo'"
        ))[0];
        $ret = $instance->has_one('test_widgets');
        $this->assertTrue($ret);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('red', $instance->test_widget->color);

        $instance = new TestModel();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_one('test_widgets');
        $this->assertTrue($ret);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('blue', $instance->test_widget->color);

        $instance = $instance->find_all(array(
            'where_clause' => "`name` = 'bar'"
        ))[0];
        $instance->has_one('test_widgets');
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('blue', $instance->test_widget->color);
    }

    public function test_has_one_by_class_name()
    {
        $instance = new TestModel();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $instance->has_one(TestWidget::class);
        $this->assertTrue($ret);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('red', $instance->test_widget->color);

        $instance = $instance->find_all(array(
            'where_clause' => "`name` = 'foo'"
        ))[0];
        $ret = $instance->has_one(TestWidget::class);
        $this->assertTrue($ret);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('red', $instance->test_widget->color);

        $instance = new TestModel();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_one(TestWidget::class);
        $this->assertTrue($ret);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('blue', $instance->test_widget->color);

        $instance = $instance->find_all(array(
            'where_clause' => "`name` = 'bar'"
        ))[0];
        $instance->has_one(TestWidget::class);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('blue', $instance->test_widget->color);
    }

    public function test_has_one_no_matches()
    {
        $instance = new TestModel();
        $instance->save();
        $ret = $instance->has_one('test_widgets');
        $this->assertFalse($ret);
        $this->assertNull($instance->test_widget);
        $instance->delete();
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

    public function test_belongs_to_by_class_name()
    {
        $instance = new TestWidget();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $instance->belongs_to(TestModel::class);
        $this->assertNotNull($instance->test_model);
        $this->assertEquals('foo', $instance->test_model->name);

        $instance = $instance->find_all(array(
            'where_clause' => "`color` = 'red'"
        ))[0];
        $instance->belongs_to(TestModel::class);
        $this->assertNotNull($instance->test_model);
        $this->assertEquals('foo', $instance->test_model->name);

        $instance = new TestWidget();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $instance->belongs_to(TestModel::class);
        $this->assertNotNull($instance->test_model);
        $this->assertEquals('bar', $instance->test_model->name);

        $instance = $instance->find_all(array(
            'where_clause' => "`color` = 'blue'"
        ))[0];
        $instance->belongs_to(TestModel::class);
        $this->assertNotNull($instance->test_model);
        $this->assertEquals('bar', $instance->test_model->name);
    }

    public function test_has_many()
    {
        $instance = new TestWidget();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $instance->has_many('test_versions');
        $this->assertTrue($ret);
        $this->assertNotNull($instance->test_versions);
        $this->assertEquals(4, count($instance->test_versions));

        $instance = new TestWidget();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_many('test_versions');
        $this->assertTrue($ret);
        $this->assertNotNull($instance->test_versions);
        $this->assertEquals(1, count($instance->test_versions));
    }

    public function test_has_many_by_class_name()
    {
        $instance = new TestWidget();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $instance->has_many(TestVersion::class);
        $this->assertTrue($ret);
        $this->assertNotNull($instance->test_versions);
        $this->assertEquals(4, count($instance->test_versions));

        $instance = new TestWidget();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_many(TestVersion::class);
        $this->assertTrue($ret);
        $this->assertNotNull($instance->test_versions);
        $this->assertEquals(1, count($instance->test_versions));
    }

    public function test_has_many_no_matches()
    {
        $instance = new TestWidget();
        $ret = $instance->find_by_id(3);
        $this->assertTrue($ret);
        $ret = $instance->has_many('test_versions');
        $this->assertFalse($ret);
        $this->assertNull($instance->test_versions);
    }

    public function test_has_and_belongs_to_many()
    {
        /*
         *  +----------+----------+-------+
         *  | model_id | group_id | count |
         *  +----------+----------+-------+
         *  |        2 |        1 |     3 |
         *  |        1 |        2 |     1 |
         *  |        2 |        2 |     0 |
         *  +----------+----------+-------+
         */
        $instance = new TestModel();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $instance->has_and_belongs_to_many('test_groups');
        $this->assertNotNull($instance->test_groups);
        $this->assertEquals(1, count($instance->test_groups));
        foreach ($instance->test_groups as $test_group) {
            $this->assertTrue(in_array($instance, $test_group->test_models));
            $this->assertTrue(isset($test_group->count));
            switch ($test_group->id) {
                case 2:
                    $this->assertEquals(1, $test_group->count);
                    break;
            }
        }

        $instance = new TestModel();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $instance->has_and_belongs_to_many('test_groups');
        $this->assertNotNull($instance->test_groups);
        $this->assertEquals(2, count($instance->test_groups));
        foreach ($instance->test_groups as $test_group) {
            $this->assertTrue(in_array($instance, $test_group->test_models));
            $this->assertTrue(isset($test_group->count));
            switch ($test_group->id) {
                case 1:
                    $this->assertEquals(3, $test_group->count);
                    break;
                case 2:
                    $this->assertEquals(0, $test_group->count);
                    break;
            }
        }
    }

    public function test_has_and_belongs_to_many_by_class_name()
    {
        /*
         *  +----------+----------+-------+
         *  | model_id | group_id | count |
         *  +----------+----------+-------+
         *  |        2 |        1 |     3 |
         *  |        1 |        2 |     1 |
         *  |        2 |        2 |     0 |
         *  +----------+----------+-------+
         */
        $instance = new TestModel();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $instance->has_and_belongs_to_many(TestGroup::class);
        $this->assertNotNull($instance->test_groups);
        $this->assertEquals(1, count($instance->test_groups));
        foreach ($instance->test_groups as $test_group) {
            $this->assertTrue(in_array($instance, $test_group->test_models));
            $this->assertTrue(isset($test_group->count));
            switch ($test_group->id) {
                case 2:
                    $this->assertEquals(1, $test_group->count);
                    break;
            }
        }

        $instance = new TestModel();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $instance->has_and_belongs_to_many(TestGroup::class);
        $this->assertNotNull($instance->test_groups);
        $this->assertEquals(2, count($instance->test_groups));
        foreach ($instance->test_groups as $test_group) {
            $this->assertTrue(in_array($instance, $test_group->test_models));
            $this->assertTrue(isset($test_group->count));
            switch ($test_group->id) {
                case 1:
                    $this->assertEquals(3, $test_group->count);
                    break;
                case 2:
                    $this->assertEquals(0, $test_group->count);
                    break;
            }
        }
    }

    public function test_has_and_belongs_to_many_inverse()
    {
        /*
         *  +----------+----------+-------+
         *  | model_id | group_id | count |
         *  +----------+----------+-------+
         *  |        2 |        1 |     3 |
         *  |        1 |        2 |     1 |
         *  |        2 |        2 |     0 |
         *  +----------+----------+-------+
         */
        $instance = new TestGroup();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $instance->has_and_belongs_to_many('test_models');
        $this->assertNotNull($instance->test_models);
        $this->assertEquals(1, count($instance->test_models));
        foreach ($instance->test_models as $test_model) {
            $this->assertTrue(in_array($instance, $test_model->test_groups));
            $this->assertTrue(isset($test_model->count));
            switch ($test_model->id) {
                case 2:
                    $this->assertEquals(3, $test_model->count);
                    break;
            }
        }

        $instance = new TestGroup();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $instance->has_and_belongs_to_many('test_models');
        $this->assertNotNull($instance->test_models);
        $this->assertEquals(2, count($instance->test_models));
        foreach ($instance->test_models as $test_model) {
            $this->assertTrue(in_array($instance, $test_model->test_groups));
            $this->assertTrue(isset($test_model->count));
            switch ($test_model->id) {
                case 1:
                    $this->assertEquals(1, $test_model->count);
                    break;
                case 2:
                    $this->assertEquals(0, $test_model->count);
                    break;
            }
        }
    }

    public function test_has_and_belongs_to_many_inverse_by_class_name()
    {
        /*
         *  +----------+----------+-------+
         *  | model_id | group_id | count |
         *  +----------+----------+-------+
         *  |        2 |        1 |     3 |
         *  |        1 |        2 |     1 |
         *  |        2 |        2 |     0 |
         *  +----------+----------+-------+
         */
        $instance = new TestGroup();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $instance->has_and_belongs_to_many(TestModel::class);
        $this->assertNotNull($instance->test_models);
        $this->assertEquals(1, count($instance->test_models));
        foreach ($instance->test_models as $test_model) {
            $this->assertTrue(in_array($instance, $test_model->test_groups));
            $this->assertTrue(isset($test_model->count));
            switch ($test_model->id) {
                case 2:
                    $this->assertEquals(3, $test_model->count);
                    break;
            }
        }

        $instance = new TestGroup();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $instance->has_and_belongs_to_many(TestModel::class);
        $this->assertNotNull($instance->test_models);
        $this->assertEquals(2, count($instance->test_models));
        foreach ($instance->test_models as $test_model) {
            $this->assertTrue(in_array($instance, $test_model->test_groups));
            $this->assertTrue(isset($test_model->count));
            switch ($test_model->id) {
                case 1:
                    $this->assertEquals(1, $test_model->count);
                    break;
                case 2:
                    $this->assertEquals(0, $test_model->count);
                    break;
            }
        }
    }

    public function test_get_initialized_from_db()
    {
        $instance = new TestModel();
        $this->assertNotNull($instance);
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $this->assertNotNull($instance->name);
    }

    public function test_get_initialized_with_values()
    {
        $instance = new TestModel(array(
            'name' => 'foo'
        ));
        $this->assertNotNull($instance);
        $this->assertEquals('foo', $instance->name);
    }

    public function test_get_not_initialized()
    {
        $instance = new TestModel();
        $this->assertNotNull($instance);
        $this->assertNull($instance->name);
    }

    public function test_get_initialized_not_a_column()
    {
        $instance = new TestModel(array(
            'foo' => 'bar'
        ));
        $this->assertNotNull($instance);
        $this->assertNull($instance->foo);
    }

    public function test_get_set_column()
    {
        $instance = new TestModel();
        $this->assertNotNull($instance);
        $instance->name = 'foo';
        $this->assertNotNull($instance->name);
        $this->assertEquals('foo', $instance->name);
    }

    public function test_get_set_not_a_column()
    {
        $instance = new TestModel();
        $this->assertNotNull($instance);
        $instance->foo = 'bar';
        $this->assertNotNull($instance->foo);
        $this->assertEquals('bar', $instance->foo);
    }

    public function test_unset()
    {
        $instance = new TestModel();
        $this->assertNotNull($instance);
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $this->assertEquals('foo', $instance->name);
        unset($instance->name);
        $this->assertTrue(!isset($instance->name));

        $instance = new TestModel();
        $this->assertNotNull($instance);
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $this->assertEquals('bar', $instance->name);
        unset($instance->name);
        $this->assertTrue(!isset($instance->name));
    }
}
?>
