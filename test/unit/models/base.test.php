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

require_once __DIR__ . '/../../utils.php';
require_once __DIR__ . '/../base_test.php';

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Db;

error_reporting(E_ALL & ~E_USER_DEPRECATED);

class ActiveRecordTest extends UnitTest
{
    private $models = [];

    function setUp(): void {}

    function teardown(): void
    {
        delete_test_models(['blip', 'baz']);
        foreach ($this->models as $model) {
            $model->delete();
        }
        $this->models = [];
    }

    public function test_construct()
    {
        $instance = new TestModel();
        $this->assertNotNull($instance);
    }

    public function test_get_column_names()
    {
        $instance = new TestModel();
        $this->assertEquals(['id', 'name', 'created_at'], $instance->get_column_names());

        $instance = new TestWidget();
        $this->assertEquals(['id', 'test_model_id', 'color', 'created_at'], $instance->get_column_names());
    }

    public function test_get_column_names_for_query()
    {
        $instance = new TestModel();
        $this->assertEquals(
            ['`test_models`.`id`', '`test_models`.`name`', '`test_models`.`created_at`'],
            $instance->get_column_names_for_query()
        );

        $instance = new TestWidget();
        $this->assertEquals(
            [
                '`test_widgets`.`id`',
                '`test_widgets`.`test_model_id`',
                '`test_widgets`.`color`',
                '`test_widgets`.`created_at`'
            ],
            $instance->get_column_names_for_query()
        );
    }

    public function test_get_column_names_for_query_with_prefix()
    {
        $instance = new TestModel();
        $this->assertEquals(
            [
                '`test_models`.`id` AS `test_models:id`',
                '`test_models`.`name` AS `test_models:name`',
                '`test_models`.`created_at` AS `test_models:created_at`'
            ],
            $instance->get_column_names_for_query(true)
        );

        $instance = new TestWidget();
        $this->assertEquals(
            [
                '`test_widgets`.`id` AS `test_widgets:id`',
                '`test_widgets`.`test_model_id` AS `test_widgets:test_model_id`',
                '`test_widgets`.`color` AS `test_widgets:color`',
                '`test_widgets`.`created_at` AS `test_widgets:created_at`'
            ],
            $instance->get_column_names_for_query(true)
        );
    }

    public function test_demux_column_names()
    {
        $row = [
            'test_models:id' => 123,
            'test_models:name' => 'Heidi',
            'test_models:created_at' => '2023-02-26 16:11:02',
            'test_widgets:id' => 456,
            'test_widgets:test_model_id' => 123,
            'test_widgets:color' => 'white',
            'test_widgets:created_at' => '1970-01-01 12:00:00'
        ];
        $instance = new TestModel();
        $this->assertEquals(
            [
                'id' => 123,
                'name' => 'Heidi',
                'created_at' => '2023-02-26 16:11:02'
            ],
            $instance->demux_column_names($row)
        );

        $instance = new TestWidget();
        $this->assertEquals(
            [
                'id' => 456,
                'test_model_id' => 123,
                'color' => 'white',
                'created_at' => '1970-01-01 12:00:00'
            ],
            $instance->demux_column_names($row)
        );
    }

    public function test_get_primary_key()
    {
        $a = new Athlete();

        $this->assertEquals('id', $a->get_primary_key());

        $b = new Runner();

        $this->assertEquals('id', $b->get_primary_key());
    }

    public function test_get_foreign_key_name()
    {
        $a = new Athlete();

        $this->assertEquals('athlete_id', $a->get_foreign_key_name());

        $b = new Runner();

        $this->assertEquals('runner_id', $b->get_foreign_key_name());
    }

    public function test_get_table_name()
    {
        $a = new Athlete();

        $this->assertEquals('athletes', $a->get_table_name());

        $b = new Runner();

        $this->assertEquals('athletes', $b->get_table_name());
    }

    public function test_get_relationship_table_half_name()
    {
        $a = new Athlete();

        $this->assertEquals('athletes', $a->get_relationship_table_half_name());

        $b = new Runner();

        $this->assertEquals('runners', $b->get_relationship_table_half_name());
    }

    public function test_save()
    {
        $instance = new TestModel([
            'name' => 'baz'
        ]);
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
        $instance = new TestModel([
            'name' => 'test_save_sets_id'
        ]);
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

        $this->assertTrue($instance->delete());
    }

    public function test_save_nullable_field_varchar_set()
    {
        $this->models[] = $model = new TestModel(['name' => 'foo']);
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
        $this->models[] = $model = new TestModel(['name' => null]);
        $model->save();

        $this->assertNull($model->name);

        $other = TestModel::find($model->id);

        $this->assertNotNull($other->id);
        $this->assertEquals($model->id, $other->id);
        $this->assertNull($other->name);
    }

    public function test_save_nullable_field_enum_set()
    {
        $this->models[] = $athlete = new Athlete(['name' => 'Alfonso', 'shirt_color' => 'red']);
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
        $this->models[] = $athlete = new Athlete(['name' => 'Alfonso', 'shirt_color' => null]);
        $athlete->save();

        $this->assertNull($athlete->shirt_color);

        $other = Athlete::find($athlete->id);

        $this->assertNotNull($other->id);
        $this->assertEquals($athlete->id, $other->id);
        $this->assertNull($other->shirt_color);
    }

    public function test_save_dupe()
    {
        $instance = new TestModel([
            'name' => 'baz'
        ]);
        $this->assertNotNull($instance, 'Model was not instantiated');
        $ret = $instance->save();
        $this->assertTrue($ret, 'Model was not saved');
        $this->assertNotNull($instance->id, 'Model id was not set');

        $other_instance = new TestModel([
            'id' => $instance->id,
            'name' => 'baz'
        ]);
        $this->assertNotNull($other_instance, 'Duplicate model was not instantiated');
        $other_instance->_force_create = true;
        $other_instance->_ignore = true;
        $ret = $other_instance->save();
        $this->assertFalse($ret, 'Duplicate model was saved and not rejected');
    }

    public function test_save_volatile()
    {
        $instance = new TestModel([
            'name' => 'baz'
        ]);
        $instance->volatile = true;

        $this->expectError();
        $this->expectErrorMessage('[TestModel::save] This model object is volatile and cannot be saved.');

        $instance->save();
    }

    public function test_delete()
    {
        create_test_model(['blip']);

        $instance = new TestModel();
        $this->assertNotNull($instance);
        $instance = $instance->find_one([
            'where_clause' => "`name`= 'blip'"
        ]);
        $this->assertNotNull($instance);
        // Ensure the call to <tt>delete</tt> returns true
        $this->assertTrue($instance->delete());
        // Ensure subsequent calls to <tt>delete</tt> return false
        $this->assertFalse($instance->delete());

        $other_instance = new TestModel();
        $this->assertNotNull($other_instance);
        $other_instance = $other_instance->find_one([
            'where_clause' => "`name`= 'blip'"
        ]);
        $this->assertNull($other_instance);
    }

    public function test_delete_unsaved()
    {
        $instance = new TestModel();
        $this->assertNotNull($instance);
        // The <tt>DELETE</tt> query will not have effect therefore <tt>delete()</tt> will do nothing
        $this->assertFalse($instance->delete());
    }

    public function test_delete_volatile()
    {
        $instance = new TestModel([
            'name' => 'baz'
        ]);
        $instance->volatile = true;

        $this->expectError();
        $this->expectErrorMessage('[TestModel::delete] This model object is volatile and cannot be deleted.');

        $this->assertTrue($instance->delete());
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
        $instances = $instance_factory->find_all([
            'where_clause' => "`name` = 'foo'"
        ]);

        $this->assertNotNull($instances);
        $this->assertEquals(1, count($instances));

        $instance = $instances[0];
        $this->assertNotNull($instance);
        $this->assertEquals('foo', $instance->name);

        $instances = $instance_factory->find_all([
            'where_clause' => "`name` = 'bar'"
        ]);

        $this->assertNotNull($instances);
        $this->assertEquals(1, count($instances));

        $instance = $instances[0];
        $this->assertNotNull($instance);
        $this->assertEquals('bar', $instance->name);
    }

    public function test_find_all_with_join()
    {
        $widget_factory = new TestWidget();
        $widgets = $widget_factory->find_all([
            'join' => TestModel::class
        ]);
        $this->assertTrue(is_array($widgets));
        foreach ($widgets as $widget) {
            $this->assertNotNull($widget->test_model);
        }
    }

    public function test_find_all_with_join_missing_fk()
    {
        $widget_factory = new TestWidget();

        $this->expectError();
        $this->expectErrorMessage(
            '[TestWidget::find_all] Failed to find a foreign key column `test_widget_id` in table `athletes` or `athlete_id` in table `test_widgets`.'
        );

        // These two models are not in a relationship so this call will trigger an error
        $widgets = $widget_factory->find_all([
            'join' => Athlete::class
        ]);
    }

    public function test_find_all_with_join_reverse()
    {
        $model_factory = new TestModel();
        $models = $model_factory->find_all([
            'join' => TestWidget::class
        ]);
        $this->assertTrue(is_array($models));
        foreach ($models as $model) {
            $this->assertNotNull($model->test_widget);
            $this->assertTrue(isset($model->id));
            $this->assertTrue(isset($model->name));
            $this->assertTrue(isset($model->created_at));
        }
    }

    public function test_find_one_no_args()
    {
        $instance_factory = new TestModel();
        $instance = $instance_factory->find_one();

        $this->assertNotNull($instance);
        $this->assertEquals('foo', $instance->name);
    }

    public function test_find_one_where_clause()
    {
        $instance_factory = new TestModel();
        $instance = $instance_factory->find_one([
            'where_clause' => "`name` = 'foo'"
        ]);

        $this->assertNotNull($instance);
        $this->assertEquals('foo', $instance->name);

        $instance = $instance_factory->find_one([
            'where_clause' => "`name` = 'bar'"
        ]);

        $this->assertNotNull($instance);
        $this->assertEquals('bar', $instance->name);
    }

    public function test_find_one_where_clause_limit_is_ignored()
    {
        $instance_factory = new TestModel();
        $instance = $instance_factory->find_one([
            'where_clause' => "`name` = 'foo'",
            'limit' => 999
        ]);

        $this->assertNotNull($instance);
        $this->assertEquals('foo', $instance->name);
    }

    public function test_find_one_with_join()
    {
        $widget_factory = new TestWidget();
        $widget = $widget_factory->find_one([
            'join' => TestModel::class
        ]);
        $this->assertNotNull($widget);
        $this->assertNotNull($widget->test_model);
    }

    public function test_find_one_with_join_reverse()
    {
        $model_factory = new TestModel();
        $model = $model_factory->find_one([
            'join' => TestWidget::class
        ]);
        $this->assertNotNull($model);
        $this->assertNotNull($model->test_widget);
        $this->assertTrue(isset($model->id));
        $this->assertTrue(isset($model->name));
        $this->assertTrue(isset($model->created_at));
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
        $count = $instance_factory->count_all([
            'where_clause' => "`name` = 'foo'"
        ]);

        $this->assertEquals(1, $count);

        $count = $instance_factory->count_all([
            'where_clause' => "`name` = 'bar'"
        ]);

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

        $instance = $instance->find_all([
            'where_clause' => "`name` = 'foo'"
        ])[0];
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

        $instance = $instance->find_all([
            'where_clause' => "`name` = 'bar'"
        ])[0];
        $instance->has_one('test_widgets');
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('blue', $instance->test_widget->color);
    }

    public function test_has_one_with_as_param()
    {
        $instance = new TestModel();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $instance->has_one('test_widgets', ['as' => 'widget']);
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->widget);
        $this->assertEquals('red', $instance->widget->color);

        $instance = $instance->find_all([
            'where_clause' => "`name` = 'foo'"
        ])[0];
        $ret = $instance->has_one('test_widgets', ['as' => 'widget']);
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->widget);
        $this->assertEquals('red', $instance->widget->color);

        $instance = new TestModel();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_one('test_widgets', ['as' => 'widget']);
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->widget);
        $this->assertEquals('blue', $instance->widget->color);

        $instance = $instance->find_all([
            'where_clause' => "`name` = 'bar'"
        ])[0];
        $instance->has_one('test_widgets', ['as' => 'widget']);
        $this->assertNotNull($instance->widget);
        $this->assertEquals('blue', $instance->widget->color);
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

        $instance = $instance->find_all([
            'where_clause' => "`name` = 'foo'"
        ])[0];
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

        $instance = $instance->find_all([
            'where_clause' => "`name` = 'bar'"
        ])[0];
        $instance->has_one(TestWidget::class);
        $this->assertNotNull($instance->test_widget);
        $this->assertEquals('blue', $instance->test_widget->color);
    }

    public function test_has_one_by_class_name_with_as_param()
    {
        $instance = new TestModel();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $instance->has_one(TestWidget::class, ['as' => 'widget']);
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->widget);
        $this->assertEquals('red', $instance->widget->color);

        $instance = $instance->find_all([
            'where_clause' => "`name` = 'foo'"
        ])[0];
        $ret = $instance->has_one(TestWidget::class, ['as' => 'widget']);
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->widget);
        $this->assertEquals('red', $instance->widget->color);

        $instance = new TestModel();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_one(TestWidget::class, ['as' => 'widget']);
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->widget);
        $this->assertEquals('blue', $instance->widget->color);

        $instance = $instance->find_all([
            'where_clause' => "`name` = 'bar'"
        ])[0];
        $instance->has_one(TestWidget::class, ['as' => 'widget']);
        $this->assertNotNull($instance->widget);
        $this->assertEquals('blue', $instance->widget->color);
    }

    public function test_has_one_with_strict_param()
    {
        // These scenarios use data from the has_many relationship
        // in order to ensure we have more than one match
        $instance = new TestWidget();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only one child expected, but found 4');
        $ret = $instance->has_one('test_versions', ['strict' => true]);
    }

    public function test_has_one_by_class_name_with_strict_param()
    {
        // These scenarios use data from the has_many relationship
        // in order to ensure we have more than one match
        $instance = new TestWidget();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only one child expected, but found 4');
        $ret = $instance->has_one(TestVersion::class, ['strict' => true]);
    }

    public function test_has_one_no_matches()
    {
        $instance = new TestModel();
        $instance->save();
        $ret = $instance->has_one('test_widgets');
        $this->assertFalse($ret);
        $this->assertNull($instance->test_widget);
        $this->assertTrue($instance->delete());
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

        $instance = $instance->find_all([
            'where_clause' => "`color` = 'red'"
        ])[0];
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

        $instance = $instance->find_all([
            'where_clause' => "`color` = 'blue'"
        ])[0];
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

        $instance = $instance->find_all([
            'where_clause' => "`color` = 'red'"
        ])[0];
        $instance->belongs_to(TestModel::class);
        $this->assertNotNull($instance->test_model);
        $this->assertEquals('foo', $instance->test_model->name);

        $instance = new TestWidget();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $instance->belongs_to(TestModel::class);
        $this->assertNotNull($instance->test_model);
        $this->assertEquals('bar', $instance->test_model->name);

        $instance = $instance->find_all([
            'where_clause' => "`color` = 'blue'"
        ])[0];
        $instance->belongs_to(TestModel::class);
        $this->assertNotNull($instance->test_model);
        $this->assertEquals('bar', $instance->test_model->name);
    }

    public function test_belongs_to_with_as_param()
    {
        $instance = new TestWidget();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $instance->belongs_to('test_models', ['as' => 'model']);
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->model);
        $this->assertEquals('foo', $instance->model->name);

        $instance = $instance->find_all([
            'where_clause' => "`color` = 'red'"
        ])[0];
        $ret = $instance->belongs_to('test_models', ['as' => 'model']);
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->model);
        $this->assertEquals('foo', $instance->model->name);

        $instance = new TestWidget();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->belongs_to('test_models', ['as' => 'model']);
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->model);
        $this->assertEquals('bar', $instance->model->name);

        $instance = $instance->find_all([
            'where_clause' => "`color` = 'blue'"
        ])[0];
        $ret = $instance->belongs_to('test_models', ['as' => 'model']);
        $this->assertIsObject($ret);
        $this->assertNotNull($instance->model);
        $this->assertEquals('bar', $instance->model->name);
    }

    public function test_belongs_to_by_class_name_with_as_param()
    {
        $instance = new TestWidget();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $instance->belongs_to(TestModel::class, ['as' => 'model']);
        $this->assertNotNull($instance->model);
        $this->assertEquals('foo', $instance->model->name);

        $instance = $instance->find_all([
            'where_clause' => "`color` = 'red'"
        ])[0];
        $instance->belongs_to(TestModel::class, ['as' => 'model']);
        $this->assertNotNull($instance->model);
        $this->assertEquals('foo', $instance->model->name);

        $instance = new TestWidget();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $instance->belongs_to(TestModel::class, ['as' => 'model']);
        $this->assertNotNull($instance->model);
        $this->assertEquals('bar', $instance->model->name);

        $instance = $instance->find_all([
            'where_clause' => "`color` = 'blue'"
        ])[0];
        $instance->belongs_to(TestModel::class, ['as' => 'model']);
        $this->assertNotNull($instance->model);
        $this->assertEquals('bar', $instance->model->name);
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

    public function test_has_many_with_as_param()
    {
        $instance = new TestWidget();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $instance->has_many(TestVersion::class, ['as' => 'versions']);
        $this->assertIsArray($ret);
        $this->assertNotNull($instance->versions);
        $this->assertEquals(4, count($instance->versions));

        $instance = new TestWidget();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_many(TestVersion::class, ['as' => 'versions']);
        $this->assertIsArray($ret);
        $this->assertNotNull($instance->versions);
        $this->assertEquals(1, count($instance->versions));
    }

    public function test_has_many_with_key_fn_param()
    {
        $instance = new TestWidget();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $instance->has_many(TestVersion::class, [
            'key_fn' => function ($version) {
                return 'v_' . $version->id;
            }
        ]);
        $this->assertIsArray($ret);
        $this->assertNotNull($instance->test_versions);
        $this->assertEquals(4, count($instance->test_versions));

        foreach ($instance->test_versions as $key => $test_version) {
            $this->assertEquals('v_' . $test_version->id, $key);
        }
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
         *  +----------+----------+-------+-------+-------------+-------+
         *  | model_id | group_id | count | color | min_version | price |
         *  +----------+----------+-------+-------+-------------+-------+
         *  |        2 |        1 |     3 | red   |        NULL |  4.99 |
         *  |        1 |        2 |     1 | green |         1.5 |  NULL |
         *  |        2 |        2 |     0 | NULL  |           2 | 10.99 |
         *  +----------+----------+-------+-------+-------------+-------+
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

                    $this->assertEquals('green', $test_group->color);
                    $this->assertTrue(is_string($test_group->color));
                    $this->assertEquals('string', gettype($test_group->color));

                    $this->assertEquals(1.5, $test_group->min_version);
                    $this->assertTrue(is_float($test_group->min_version));
                    $this->assertEquals('double', gettype($test_group->min_version));

                    $this->assertNull($test_group->price);
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

                    $this->assertEquals('red', $test_group->color);
                    $this->assertTrue(is_string($test_group->color));
                    $this->assertEquals('string', gettype($test_group->color));

                    $this->assertNull($test_group->min_version);

                    $this->assertEquals(4.99, $test_group->price);
                    $this->assertTrue(is_float($test_group->price));
                    $this->assertEquals('double', gettype($test_group->price));
                    break;
                case 2:
                    $this->assertEquals(0, $test_group->count);

                    $this->assertNull($test_group->color);

                    $this->assertEquals(2.0, $test_group->min_version);
                    $this->assertTrue(is_float($test_group->min_version));
                    $this->assertEquals('double', gettype($test_group->min_version));

                    $this->assertEquals(10.99, $test_group->price);
                    $this->assertTrue(is_float($test_group->price));
                    $this->assertEquals('double', gettype($test_group->price));
                    break;
            }
        }
    }

    public function test_has_and_belongs_to_many_by_class_name()
    {
        /*
         *  +----------+----------+-------+-------+-------------+-------+
         *  | model_id | group_id | count | color | min_version | price |
         *  +----------+----------+-------+-------+-------------+-------+
         *  |        2 |        1 |     3 | red   |        NULL |  4.99 |
         *  |        1 |        2 |     1 | green |         1.5 |  NULL |
         *  |        2 |        2 |     0 | NULL  |           2 | 10.99 |
         *  +----------+----------+-------+-------+-------------+-------+
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

                    $this->assertEquals('green', $test_group->color);
                    $this->assertTrue(is_string($test_group->color));
                    $this->assertEquals('string', gettype($test_group->color));

                    $this->assertEquals(1.5, $test_group->min_version);
                    $this->assertTrue(is_float($test_group->min_version));
                    $this->assertEquals('double', gettype($test_group->min_version));

                    $this->assertNull($test_group->price);
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

                    $this->assertEquals('red', $test_group->color);
                    $this->assertTrue(is_string($test_group->color));
                    $this->assertEquals('string', gettype($test_group->color));

                    $this->assertNull($test_group->min_version);

                    $this->assertEquals(4.99, $test_group->price);
                    $this->assertTrue(is_float($test_group->price));
                    $this->assertEquals('double', gettype($test_group->price));
                    break;
                case 2:
                    $this->assertEquals(0, $test_group->count);

                    $this->assertNull($test_group->color);

                    $this->assertEquals(2.0, $test_group->min_version);
                    $this->assertTrue(is_float($test_group->min_version));
                    $this->assertEquals('double', gettype($test_group->min_version));

                    $this->assertEquals(10.99, $test_group->price);
                    $this->assertTrue(is_float($test_group->price));
                    $this->assertEquals('double', gettype($test_group->price));
                    break;
            }
        }
    }

    public function test_has_and_belongs_to_many_with_as_param()
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
        $ret = $instance->has_and_belongs_to_many(TestGroup::class, [
            'as' => 'groups'
        ]);
        $this->assertIsArray($ret);
        $this->assertNotNull($instance->groups);
        $this->assertEquals(1, count($instance->groups));
        foreach ($instance->groups as $test_group) {
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
        $ret = $instance->has_and_belongs_to_many(TestGroup::class, [
            'as' => 'groups'
        ]);
        $this->assertIsArray($ret);
        $this->assertNotNull($instance->groups);
        $this->assertEquals(2, count($instance->groups));
        foreach ($instance->groups as $test_group) {
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

    public function test_has_and_belongs_to_many_with_key_fn_param()
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
        $ret = $instance->has_and_belongs_to_many(TestGroup::class, [
            'key_fn' => function ($group) {
                return 'gid_' . $group->id;
            }
        ]);
        $this->assertIsArray($ret);
        $this->assertNotNull($instance->test_groups);
        $this->assertEquals(1, count($instance->test_groups));
        foreach ($instance->test_groups as $key => $test_group) {
            $this->assertEquals('gid_' . $test_group->id, $key);
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

    public function test_has_and_belongs_to_many_join_widgets()
    {
        /*
         *  TABLE `test_groups_test_models`
         *  +----------+----------+-------+
         *  | model_id | group_id | count |
         *  +----------+----------+-------+
         *  |        2 |        1 |     3 |
         *  |        1 |        2 |     1 |
         *  |        2 |        2 |     0 |
         *  +----------+----------+-------+
         *
         *  TABLE `test_widgets`
         *  +----------+----------+
         *  | id       | model_id |
         *  +----------+----------+
         *  |        1 |        1 |
         *  |        2 |        2 |
         *  |        3 |        2 |
         *  +----------+----------+
         */
        $instance = new TestGroup();
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $instance->has_and_belongs_to_many(TestModel::class, [
            'join' => TestWidget::class
        ]);
        $this->assertIsArray($ret);
        $this->assertNotNull($instance->test_models);
        $this->assertEquals(1, count($instance->test_models));
        foreach ($instance->test_models as $test_model) {
            $this->assertTrue(in_array($instance, $test_model->test_groups));
            $this->assertTrue(isset($test_model->count));
            switch ($test_model->id) {
                case 2:
                    $this->assertEquals(3, $test_model->count);
                    $this->assertNotNull($test_model->test_widget);
                    $this->assertEquals('green', $test_model->test_widget->color);
                    break;
            }
        }

        $instance = new TestGroup();
        $ret = $instance->find_by_id(2);
        $this->assertTrue($ret);
        $ret = $instance->has_and_belongs_to_many(TestModel::class, [
            'join' => TestWidget::class
        ]);
        $this->assertIsArray($ret);
        $this->assertNotNull($instance->test_models);
        $this->assertEquals(2, count($instance->test_models));
        foreach ($instance->test_models as $test_model) {
            $this->assertTrue(in_array($instance, $test_model->test_groups));
            $this->assertTrue(isset($test_model->count));
            switch ($test_model->id) {
                case 1:
                    $this->assertEquals(1, $test_model->count);
                    $this->assertNotNull($test_model->test_widget);
                    $this->assertEquals('red', $test_model->test_widget->color);
                    break;
                case 2:
                    $this->assertEquals(0, $test_model->count);
                    $this->assertNotNull($test_model->test_widget);
                    $this->assertEquals('blue', $test_model->test_widget->color);
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
        $instance = new TestModel([
            'name' => 'foo'
        ]);
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
        $instance = new TestModel([
            'foo' => 'bar'
        ]);
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
        $instance = new TestModel(['name' => 'blip']);
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

    public function test_debug_info()
    {
        $instance = new TestModel();
        $this->assertNotNull($instance);
        $ret = $instance->find_by_id(1);
        $this->assertTrue($ret);
        ob_start();
        print_r($instance);
        $printed = ob_get_clean();
        $this->assertNotNull($printed);
        $this->assertThat(
            $printed,
            $this->matchesRegularExpression(
                <<<EOT
                /TestModel Object
                \(
                    \[id\] => 1
                    \[name\] => foo
                    \[created_at\] => \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}
                \)
                /
                EOT
            )
        );

        ob_start();
        var_dump($instance);
        $printed = ob_get_clean();
        $this->assertNotNull($printed);
        $this->assertThat(
            $printed,
            $this->matchesRegularExpression(
                <<<EOT
                /object\(TestModel\)#\d+ \(3\) \{
                  \["id"\]=>
                  int\(1\)
                  \["name"\]=>
                  string\(3\) "foo"
                  \["created_at"\]=>
                  string\(19\) "\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}"
                \}
                /
                EOT
            )
        );
    }

    public function test_validate_on_save_null_for_not_nullable()
    {
        $this->models[] = $athlete = new Athlete([
            'name' => 'Marcell',
            'weight' => null
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Attempt to null the field 'weight' but it is not nullable");

        // Throws:
        $athlete->save();
    }

    public function test_validate_on_set_string_for_int()
    {
        $this->models[] = $athlete = new Athlete([
            'name' => 'Marcell'
        ]);

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
        $this->models[] = $athlete = new Athlete([
            'name' => 'Marcell'
        ]);

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
        $this->models[] = $athlete = new Athlete([
            'name' => 'Marcell'
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Attempt to set the field 'shirt_color' to a value with incorrect type. Expected 'enum('red','green','blue')' but found: 'integer'"
        );

        // This is okay:
        $athlete->shirt_color = 'red';

        // This throws:
        $athlete->shirt_color = 123;
    }

    public function test_validate_on_set_string_for_enum()
    {
        $this->models[] = $athlete = new Athlete([
            'name' => 'Marcell'
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Attempt to set the field 'shirt_color' to a value with incorrect type. Expected 'enum('red','green','blue')' but found: 'string'"
        );

        // This is okay:
        $athlete->shirt_color = 'red';

        // This throws:
        $athlete->shirt_color = 'orange';
    }

    public function test_validate_on_set_null_for_not_nullable()
    {
        $this->models[] = $athlete = new Athlete([
            'name' => 'Marcell'
        ]);

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

        $models = [];

        for ($i = 0; $i < 100; $i++) {
            $model = new TestModel([
                'name' => 'baz' . $i
            ]);
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
            $this->assertTrue($model->delete());
        }

        // Pool is empty again
        $this->assertEquals(0, TestModel::get_pool_stats(TestModel::class)['count']);

        Config::set('OBJECT_POOL_ENABLED', $enabled);
    }

    public function test_as_valid_superclass()
    {
        $r = new Runner();

        $this->assertEquals('Runner', get_class($r));

        $this->assertEquals('Athlete', get_class($r->as(Athlete::class)));
    }

    public function test_as_invalid_superclass()
    {
        $r = new Runner();

        $this->assertEquals('Runner', get_class($r));

        $this->expectError();
        $this->expectErrorMessage(
            '[Runner::as] Attempted to cast an instance of Runner to TestWidget but it is not a valid superclass.'
        );

        // Triggers
        $r->as(TestWidget::class);
    }
}
