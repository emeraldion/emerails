<?php
/**
 * @format
 */

require_once __DIR__ . '/../../utils.php';
require_once __DIR__ . '/../base_test.php';

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Db;

error_reporting(E_ALL & ~E_USER_DEPRECATED);

class ActiveRecordTest extends UnitTest
{
    private $models = array();

    function setUp(): void
    {
    }

    function teardown(): void
    {
        delete_test_models(array('blip', 'baz'));
        foreach ($this->models as $model) {
            $model->delete();
        }
        $this->models = array();
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

    public function test_save_nullable_field_varchar_set()
    {
        $this->models[] = $model = new TestModel(array('name' => 'foo'));
        $model->save();

        $this->assertNotNull($model->name);
        $this->assertEquals('foo', $model->name);

        $model->name = null;
        $model->save();

        $this->assertNull($model->name);

        $other = TestModel::find($model->id);

        $this->assertNotNull($other->id);
        $this->assertEquals($model->id, $other->id);
        $this->assertNull($other->name);
    }

    public function test_save_nullable_field_varchar_construct()
    {
        $this->models[] = $model = new TestModel(array('name' => null));
        $model->save();

        $this->assertNull($model->name);

        $other = TestModel::find($model->id);

        $this->assertNotNull($other->id);
        $this->assertEquals($model->id, $other->id);
        $this->assertNull($other->name);
    }

    public function test_save_nullable_field_enum_set()
    {
        $this->models[] = $athlete = new Athlete(array('name' => 'Alfonso', 'shirt_color' => 'red'));
        $athlete->save();

        $this->assertNotNull($athlete->shirt_color);
        $this->assertEquals('red', $athlete->shirt_color);

        $athlete->shirt_color = null;
        $athlete->save();

        $this->assertNull($athlete->shirt_color);

        $other = Athlete::find($athlete->id);

        $this->assertNotNull($other->id);
        $this->assertEquals($athlete->id, $other->id);
        $this->assertNull($other->shirt_color);
    }

    public function test_save_nullable_field_enum_construct()
    {
        $this->models[] = $athlete = new Athlete(array('name' => 'Alfonso', 'shirt_color' => null));
        $athlete->save();

        $this->assertNull($athlete->shirt_color);

        $other = Athlete::find($athlete->id);

        $this->assertNotNull($other->id);
        $this->assertEquals($athlete->id, $other->id);
        $this->assertNull($other->shirt_color);
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

    public function test_static_find()
    {
        $instance = TestModel::find(1);
        $this->assertNotNull($instance);
        $this->assertEquals(TestModel::class, get_class($instance));
        $this->assertEquals('foo', $instance->name);

        $instance = TestModel::find(2);
        $this->assertNotNull($instance);
        $this->assertEquals(TestModel::class, get_class($instance));
        $this->assertEquals('bar', $instance->name);
    }

    public function test_static_find_with_class_name()
    {
        $instance = TestModel::find(1, 'TestModel');
        $this->assertNotNull($instance);
        $this->assertEquals(TestModel::class, get_class($instance));
        $this->assertEquals('foo', $instance->name);

        $instance = TestModel::find(2, 'TestModel');
        $this->assertNotNull($instance);
        $this->assertEquals(TestModel::class, get_class($instance));
        $this->assertEquals('bar', $instance->name);
    }

    public function test_static_find_no_match()
    {
        $instance = TestModel::find(3);
        $this->assertNull($instance);
    }

    public function test_static_find_no_match_with_class_name()
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
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('red', $instance->test_widget->color);

        $instance = $instance->find_all(array(
            'where_clause' => "`name` = 'foo'"
        ))[0];
        $ret = $instance->has_one('test_widgets');
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('red', $instance->test_widget->color);

        $instance = new TestModel();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_one('test_widgets');
        $this->assertIsObject($ret);
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
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('red', $instance->test_widget->color);

        $instance = $instance->find_all(array(
            'where_clause' => "`name` = 'foo'"
        ))[0];
        $ret = $instance->has_one(TestWidget::class);
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('red', $instance->test_widget->color);

        $instance = new TestModel();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_one(TestWidget::class);
        $this->assertIsObject($ret);
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
        $ret = $instance->belongs_to('test_models');
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->test_model);
        $this->assertEquals('foo', $instance->test_model->name);

        $instance = $instance->find_all(array(
            'where_clause' => "`color` = 'red'"
        ))[0];
        $ret = $instance->belongs_to('test_models');
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->test_model);
        $this->assertEquals('foo', $instance->test_model->name);

        $instance = new TestWidget();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->belongs_to('test_models');
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->test_model);
        $this->assertEquals('bar', $instance->test_model->name);

        $instance = $instance->find_all(array(
            'where_clause' => "`color` = 'blue'"
        ))[0];
        $ret = $instance->belongs_to('test_models');
        $this->assertIsObject($ret);
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
        $this->assertIsArray($ret);
        $this->assertNotNull($instance->test_versions);
        $this->assertEquals(4, count($instance->test_versions));

        $instance = new TestWidget();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_many('test_versions');
        $this->assertIsArray($ret);
        $this->assertNotNull($instance->test_versions);
        $this->assertEquals(1, count($instance->test_versions));
    }

    public function test_has_many_by_class_name()
    {
        $instance = new TestWidget();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $instance->has_many(TestVersion::class);
        $this->assertIsArray($ret);
        $this->assertNotNull($instance->test_versions);
        $this->assertEquals(4, count($instance->test_versions));

        $instance = new TestWidget();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_many(TestVersion::class);
        $this->assertIsArray($ret);
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
        $ret = $instance->has_and_belongs_to_many('test_groups');
        $this->assertIsArray($ret);
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
        $ret = $instance->has_and_belongs_to_many('test_groups');
        $this->assertIsArray($ret);
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
        $ret = $instance->has_and_belongs_to_many(TestGroup::class);
        $this->assertIsArray($ret);
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
        $ret = $instance->has_and_belongs_to_many(TestGroup::class);
        $this->assertIsArray($ret);
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
        $ret = $instance->has_and_belongs_to_many('test_models');
        $this->assertIsArray($ret);
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
        $ret = $instance->has_and_belongs_to_many('test_models');
        $this->assertIsArray($ret);
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
        $ret = $instance->has_and_belongs_to_many(TestModel::class);
        $this->assertIsArray($ret);
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
        $ret = $instance->has_and_belongs_to_many(TestModel::class);
        $this->assertIsArray($ret);
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

    public function test_isset()
    {
        $instance = new TestModel();
        $this->assertFalse(isset($instance->name));
        $this->assertFalse(isset($instance->foo));
        $instance->name = 'foo';
        $this->assertTrue(isset($instance->name));
        $this->assertFalse(isset($instance->foo));
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

    public function test_get_set_nullable_column()
    {
        $instance = new TestModel(array('name' => 'blip'));
        $this->assertNotNull($instance);
        $this->assertNotNull($instance->name);
        $instance->name = null;
        $this->assertNull($instance->name);
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

    public function test_validate_on_save_null_for_not_nullable()
    {
        $this->models[] = $athlete = new Athlete(array(
            'name' => 'Marcell',
            'weight' => null
        ));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Attempt to null the field 'weight' but it is not nullable");

        // Throws:
        $athlete->save();
    }

    public function test_validate_on_set_string_for_int()
    {
        $this->models[] = $athlete = new Athlete(array(
            'name' => 'Marcell'
        ));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Attempt to set the field 'weight' to a value with incorrect type. Expected 'int(11)' but found: 'string'"
        );

        // This is okay:
        $athlete->weight = 123;

        // Throws:
        $athlete->weight = '123';
    }

    public function test_validate_on_set_string_for_float()
    {
        $this->models[] = $athlete = new Athlete(array(
            'name' => 'Marcell'
        ));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Attempt to set the field 'height' to a value with incorrect type. Expected 'float' but found: 'string'"
        );

        // This is okay:
        $athlete->height = 123.456;

        // This throws:
        $athlete->height = '123.456';
    }

    public function test_validate_on_set_int_for_enum()
    {
        $this->models[] = $athlete = new Athlete(array(
            'name' => 'Marcell'
        ));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Field 'shirt_color' has the wrong type. Expected 'enum('red','green','blue')' but found: 'integer'"
        );

        // This is okay:
        $athlete->shirt_color = 'red';

        // This throws:
        $athlete->shirt_color = 123;
    }

    public function test_validate_on_set_string_for_enum()
    {
        $this->models[] = $athlete = new Athlete(array(
            'name' => 'Marcell'
        ));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Field 'shirt_color' has the wrong type. Expected 'enum('red','green','blue')' but found: 'string'"
        );

        // This is okay:
        $athlete->shirt_color = 'red';

        // This throws:
        $athlete->shirt_color = 'orange';
    }

    public function test_validate_on_set_null_for_not_nullable()
    {
        $this->models[] = $athlete = new Athlete(array(
            'name' => 'Marcell'
        ));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Attempt to null the field 'weight' but it is not nullable");

        // This is okay:
        $athlete->weight = 123;

        // Throws:
        $athlete->weight = null;
    }

    public function test_object_pool()
    {
        $enabled = Config::get('OBJECT_POOL_ENABLED');
        Config::set('OBJECT_POOL_ENABLED', true);

        TestModel::_purge_pool(TestModel::class);
        $this->assertEquals(0, TestModel::get_pool_stats(TestModel::class)['count']);

        $models = array();

        for ($i = 0; $i < 100; $i++) {
            $model = new TestModel(array(
                'name' => 'baz' . $i
            ));
            $model->save();

            $this->models[] = $models[] = $model;
        }

        // Pool is still empty
        $this->assertEquals(0, TestModel::get_pool_stats(TestModel::class)['count']);

        foreach ($models as $model) {
            TestModel::find($model->id);
        }

        // Pool contains all models created so far
        $this->assertEquals(count($models), TestModel::get_pool_stats(TestModel::class)['count']);

        foreach ($models as $model) {
            $model->delete();
        }

        // Pool is empty again
        $this->assertEquals(0, TestModel::get_pool_stats(TestModel::class)['count']);

        Config::set('OBJECT_POOL_ENABLED', $enabled);
    }
}
?>
