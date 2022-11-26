<?php
/**
 * @format
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/db.conf.php';
require_once __DIR__ . '/../../utils.php';

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Db;
use Emeraldion\EmeRails\DbAdapters\MysqlAdapter;
use Emeraldion\EmeRails\DbAdapters\MysqliAdapter;
use Emeraldion\EmeRails\Models\ActiveRecord;
use Emeraldion\EmeRails\Models\Relationship;

Db::register_adapter(new MysqlAdapter(), MysqlAdapter::NAME);
Db::register_adapter(new MysqliAdapter(), MysqliAdapter::NAME);

class RelationshipTest extends \PHPUnit\Framework\TestCase
{
    private $models = array();

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
        array_walk($this->models, function ($model) {
            $model->delete();
        });
    }

    public function test_private_constructor()
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage("Call to private Relationship::__construct() from context 'RelationshipTest'");
        new Relationship(TestModel::class, TestWidget::class);
    }

    public function test_one_to_one()
    {
        $r = Relationship::one_to_one(TestWidget::class, TestVersion::class);
        $this->assertNotNull($r);
    }

    public function test_one_to_many()
    {
        $r = Relationship::one_to_many(TestWidget::class, TestVersion::class);
        $this->assertNotNull($r);
    }

    public function test_many_to_many()
    {
        $r = Relationship::many_to_many(TestWidget::class, TestVersion::class);
        $this->assertNotNull($r);
    }

    public function test_get_table_name_one_to_one()
    {
        $r = Relationship::one_to_one(TestModel::class, TestWidget::class);
        $this->assertNull($r->get_table_name());
    }

    public function test_get_table_name_one_to_many()
    {
        $r = Relationship::one_to_many(TestModel::class, TestWidget::class);
        $this->assertNull($r->get_table_name());
    }

    public function test_get_table_name_many_to_many()
    {
        $r = Relationship::many_to_many(TestModel::class, TestGroup::class);
        $this->assertNotNull($r->get_table_name());
        $this->assertEquals('test_groups_test_models', $r->get_table_name());
    }

    public function test_get_table_name_many_to_many_reverse_order()
    {
        $r = Relationship::many_to_many(TestGroup::class, TestModel::class);
        $this->assertNotNull($r->get_table_name());
        $this->assertEquals('test_groups_test_models', $r->get_table_name());
    }

    public function test_between_wrong_class()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Argument 1 expected of class 'TestModel' or 'TestWidget', but got 'TestGroup' instead."
        );
        $r = Relationship::one_to_one(TestModel::class, TestWidget::class);
        $model = new TestModel();
        $group = new TestGroup();
        $r->between($group, $model);
    }

    public function test_between_wrong_class_reverse_order()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Argument 2 expected of class 'TestModel' or 'TestWidget', but got 'TestGroup' instead."
        );
        $r = Relationship::one_to_one(TestModel::class, TestWidget::class);
        $model = new TestModel();
        $group = new TestGroup();
        $r->between($model, $group);
    }

    public function test_save_one_to_one()
    {
        $r = Relationship::one_to_one(TestWidget::class, TestVersion::class);

        $version = new TestVersion(array('version' => '0.0.1'));
        $version->save();

        $widget = new TestWidget(array(
            'color' => 'khaki'
        ));
        $widget->save();

        $this->models[] = $version;
        $this->models[] = $widget;

        $instance = $r->between($widget, $version);

        // Save
        $instance->save();

        $widget->has_one(TestVersion::class);
        $this->assertNotNull($widget->test_version);
        $this->assertEquals($version->version, $widget->test_version->version);

        $version->belongs_to(TestWidget::class);
        $this->assertNotNull($version->test_widget);
        $this->assertEquals($widget->color, $version->test_widget->color);
    }

    public function test_save_one_to_one_missing_fk_column()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Cannot find a column 'test_group_id' in table 'test_models' or a column 'test_model_id' in table 'test_groups'."
        );

        $r = Relationship::one_to_one(TestGroup::class, TestModel::class);

        $model = new TestModel();
        $model->save();

        $group = new TestGroup();
        $group->save();

        $this->models[] = $model;
        $this->models[] = $group;

        $instance = $r->between($group, $model);

        // This will throw
        $instance->save();
    }

    public function test_save_one_to_many()
    {
        $r = Relationship::one_to_one(TestWidget::class, TestVersion::class);

        $v1 = new TestVersion(array('version' => '1'));
        $v1->save();

        $v2 = new TestVersion(array('version' => '1'));
        $v2->save();

        $widget = new TestWidget(array(
            'color' => 'mauve'
        ));
        $widget->save();

        $this->models[] = $v1;
        $this->models[] = $v2;
        $this->models[] = $widget;

        $i1 = $r->between($widget, $v1);
        $i2 = $r->between($widget, $v2);

        // Save
        $i1->save();
        $i2->save();

        $widget->has_many(TestVersion::class);
        $this->assertNotNull($widget->test_versions);
        $this->assertEquals(2, count($widget->test_versions));
        for ($i = 0; $i < 2; $i++) {
            $this->assertEquals($version->version, $widget->test_versions[$i]->version);
        }
        $this->assertTrue(array_key_exists($v1->id, $widget->test_versions));
        $this->assertTrue(array_key_exists($v2->id, $widget->test_versions));

        $v1->belongs_to(TestWidget::class);
        $this->assertNotNull($v1->test_widget);
        $this->assertEquals($widget->color, $v1->test_widget->color);

        $v2->belongs_to(TestWidget::class);
        $this->assertNotNull($v2->test_widget);
        $this->assertEquals($widget->color, $v2->test_widget->color);
    }

    public function test_save_many_to_many()
    {
        $r = Relationship::many_to_many(TestGroup::class, TestModel::class);

        $model = new TestModel();
        $model->save();

        $group = new TestGroup();
        $group->save();

        $this->models[] = $model;
        $this->models[] = $group;

        // These are both valid
        $instance = $r->between($group, $model);
        // $instance = $r->between($model, $group);

        // But also (TBD):
        // $model->in_many_to_many_with($group);
        // $group->in_many_to_many_with($model);

        // Save
        $instance->save();

        $this->models[] = $instance;

        $model->has_and_belongs_to_many(TestGroup::class);
        $this->assertNotNull($model->test_groups);
        $this->assertEquals(1, count($model->test_groups));
        $this->assertEquals($group->name, $model->test_groups[0]->name);
        $this->assertTrue(array_key_exists($group->id, $model->test_groups));
        $this->assertEquals($group->name, $model->test_groups[$group->id]->name);

        $group->has_and_belongs_to_many(TestModel::class);
        $this->assertNotNull($group->test_models);
        $this->assertEquals(1, count($group->test_models));
        $this->assertEquals($model->name, $group->test_models[0]->name);
        $this->assertTrue(array_key_exists($model->id, $group->test_models));
        $this->assertEquals($model->name, $group->test_models[$model->id]->name);
    }

    public function test_save_many_to_many_multiple()
    {
        $r = Relationship::many_to_many(TestGroup::class, TestModel::class);

        $models = array();
        for ($i = 0; $i < 2; $i++) {
            $m = new TestModel();
            $m->save();
            $models[] = $m;
            $this->models[] = $m;
        }

        $groups = array();
        for ($j = 0; $j < 3; $j++) {
            $g = new TestGroup();
            $g->save();
            $groups[] = $g;
            $this->models[] = $g;
        }

        for ($i = 0; $i < 2; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $instance = $r->between($groups[$j], $models[$i]);
                $instance->save();
                $this->models[] = $instance;
            }
        }

        for ($i = 0; $i < 2; $i++) {
            $models[$i]->has_and_belongs_to_many(TestGroup::class);
            $this->assertNotNull($models[$i]->test_groups);
            $this->assertEquals(3, count($models[$i]->test_groups));
            for ($j = 0; $j < 3; $j++) {
                $this->assertEquals($groups[$j]->name, $models[$i]->test_groups[$j]->name);
                $this->assertTrue(array_key_exists($groups[$j]->id, $models[$i]->test_groups));
                $this->assertEquals($groups[$j]->name, $models[$i]->test_groups[$groups[$j]->id]->name);
            }
        }

        for ($j = 0; $j < 3; $j++) {
            $groups[$j]->has_and_belongs_to_many(TestModel::class);
            $this->assertNotNull($groups[$j]->test_models);
            $this->assertEquals(2, count($groups[$j]->test_models));
            for ($i = 0; $i < 2; $i++) {
                $this->assertEquals($models[$i]->name, $groups[$j]->test_models[$i]->name);
                $this->assertTrue(array_key_exists($models[$i]->id, $groups[$j]->test_models));
                $this->assertEquals($models[$i]->name, $groups[$j]->test_models[$models[$i]->id]->name);
            }
        }
    }
}
?>
