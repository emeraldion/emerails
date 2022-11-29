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

    public function test_among_wrong_class()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Argument 1 expected of class 'TestModel' or 'TestWidget', but got 'TestGroup' instead."
        );
        $r = Relationship::one_to_many(TestModel::class, TestWidget::class);
        $models = array(new TestModel(), new TestModel());
        $groups = array(new TestGroup(), new TestGroup(), new TestGroup());
        $r->among($groups, $models);
    }

    public function test_among_wrong_class_reverse_order()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Argument 2 expected of class 'TestModel' or 'TestWidget', but got 'TestGroup' instead."
        );
        $r = Relationship::one_to_many(TestModel::class, TestWidget::class);
        $models = array(new TestModel(), new TestModel());
        $groups = array(new TestGroup(), new TestGroup(), new TestGroup());
        $r->among($models, $groups);
    }

    public function test_among_throws_in_one_to_one_relationship()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This relationship has cardinality one to one.');
        Relationship::one_to_one(TestModel::class, TestWidget::class)->among(new TestModel(), new TestGroup());
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

        $v2 = new TestVersion(array('version' => '2'));
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

        $versions = array($v1, $v2);

        $widget->has_many(TestVersion::class);
        $this->assertNotNull($widget->test_versions);
        $this->assertEquals(2, count($widget->test_versions));
        for ($i = 0; $i < 2; $i++) {
            $this->assertTrue(array_key_exists($versions[$i]->id, $widget->test_versions));
            $this->assertEquals($versions[$i]->version, $widget->test_versions[$versions[$i]->id]->version);
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

    public function test_save_one_to_many_missing_fk_column()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Cannot find a column 'test_version_id' in table 'test_models' or a column 'test_model_id' in table 'test_versions'."
        );

        $r = Relationship::one_to_many(TestVersion::class, TestModel::class);

        $model = new TestModel();
        $model->save();

        $version = new TestVersion(array('version' => '1.2.3'));
        $version->save();

        $this->models[] = $model;
        $this->models[] = $version;

        $instance = $r->between($version, $model);

        // This will throw
        $instance->save();
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

        // Save
        $instance->save();

        $this->models[] = $instance;

        $model->has_and_belongs_to_many(TestGroup::class);
        $this->assertNotNull($model->test_groups);
        $this->assertEquals(1, count($model->test_groups));

        list($tg) = array_values($model->test_groups);
        $this->assertEquals($group->name, $tg->name);
        $this->assertTrue(array_key_exists($group->id, $model->test_groups));
        $this->assertEquals($group->name, $model->test_groups[$group->id]->name);

        $group->has_and_belongs_to_many(TestModel::class);
        $this->assertNotNull($group->test_models);
        $this->assertEquals(1, count($group->test_models));

        list($tm) = array_values($group->test_models);
        $this->assertEquals($model->name, $tm->name);
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
                $this->assertEquals($groups[$j]->name, array_values($models[$i]->test_groups)[$j]->name);
                $this->assertTrue(array_key_exists($groups[$j]->id, $models[$i]->test_groups));
                $this->assertEquals($groups[$j]->name, $models[$i]->test_groups[$groups[$j]->id]->name);
            }
        }

        for ($j = 0; $j < 3; $j++) {
            $groups[$j]->has_and_belongs_to_many(TestModel::class);
            $this->assertNotNull($groups[$j]->test_models);
            $this->assertEquals(2, count($groups[$j]->test_models));
            for ($i = 0; $i < 2; $i++) {
                $this->assertEquals($models[$i]->name, array_values($groups[$j]->test_models)[$i]->name);
                $this->assertTrue(array_key_exists($models[$i]->id, $groups[$j]->test_models));
                $this->assertEquals($models[$i]->name, $groups[$j]->test_models[$models[$i]->id]->name);
            }
        }
    }

    public function test_save_many_to_many_with_params()
    {
        $r = Relationship::many_to_many(TestGroup::class, TestModel::class);

        $model = new TestModel();
        $model->save();

        $group = new TestGroup();
        $group->save();

        $this->models[] = $model;
        $this->models[] = $group;

        // These are both valid
        $instance = $r->between($group, $model, array('count' => 12));
        // $instance = $r->between($model, $group);

        // Save
        $instance->save();

        $this->models[] = $instance;

        $model->has_and_belongs_to_many(TestGroup::class);
        $this->assertNotNull($model->test_groups);
        $this->assertEquals(1, count($model->test_groups));

        list($tg) = array_values($model->test_groups);
        $this->assertEquals($group->name, $tg->name);
        $this->assertTrue(array_key_exists($group->id, $model->test_groups));
        $this->assertEquals($group->name, $model->test_groups[$group->id]->name);
        $this->assertEquals(12, $model->test_groups[$group->id]->count);

        $group->has_and_belongs_to_many(TestModel::class);
        $this->assertNotNull($group->test_models);
        $this->assertEquals(1, count($group->test_models));

        list($tm) = array_values($group->test_models);
        $this->assertEquals($model->name, $tm->name);
        $this->assertTrue(array_key_exists($model->id, $group->test_models));
        $this->assertEquals($model->name, $group->test_models[$model->id]->name);
        $this->assertEquals(12, $group->test_models[$model->id]->count);
    }

    public function test_has_one_delete()
    {
        $r = Relationship::one_to_one(TestModel::class, TestWidget::class);

        $model = new TestModel();
        $model->save();
        $this->models[] = $model;

        $widget = new TestWidget(array(
            'color' => 'pink'
        ));
        $widget->save();
        $this->models[] = $widget;

        $instance = $r->between($model, $widget);
        $instance->save();
        $this->models[] = $instance;

        $ret = $model->has_one(TestWidget::class);
        $this->assertIsObject($ret);
        $this->assertNotNull($model->test_widget);
        $this->assertEquals($model->test_widget->id, $widget->id);
        $this->assertEquals($model->test_widget->color, $widget->color);

        $ret->delete();

        $ret = $model->has_one(TestWidget::class);
        $this->assertNull($model->test_widget);
    }

    public function test_belongs_to_delete()
    {
        $r = Relationship::one_to_one(TestModel::class, TestWidget::class);

        $model = new TestModel();
        $model->save();
        $this->models[] = $model;

        $widget = new TestWidget(array(
            'color' => 'pink'
        ));
        $widget->save();
        $this->models[] = $widget;

        $instance = $r->between($model, $widget);
        $instance->save();
        $this->models[] = $instance;

        $ret = $widget->belongs_to(TestModel::class);
        $this->assertIsObject($ret);
        $this->assertNotNull($widget->test_model);
        $this->assertEquals($widget->test_model->id, $model->id);

        $ret->delete();

        $ret = $widget->belongs_to(TestModel::class);
        $this->assertFalse($ret);
        $this->assertNull($widget->test_model);
    }

    public function test_has_many_delete()
    {
        $r = Relationship::one_to_one(TestModel::class, TestWidget::class);

        $model = new TestModel();
        $model->save();
        $this->models[] = $model;

        $w1 = new TestWidget(array(
            'color' => 'pink'
        ));
        $w1->save();
        $this->models[] = $w1;

        $w2 = new TestWidget(array(
            'color' => 'maroon'
        ));
        $w2->save();
        $this->models[] = $w2;

        $instance = $r->between($model, $w1);
        $instance->save();
        $this->models[] = $instance;

        $instance = $r->between($model, $w2);
        $instance->save();
        $this->models[] = $instance;

        $ret = $model->has_many(TestWidget::class);
        $this->assertIsArray($ret);
        $this->assertNotNull($model->test_widgets);
        $this->assertIsArray($model->test_widgets);
        $this->assertEquals(2, count($model->test_widgets));
        $this->assertTrue(array_key_exists($w1->id, $model->test_widgets));
        $this->assertTrue(array_key_exists($w2->id, $model->test_widgets));

        array_walk($ret[$model->id], function ($r) {
            $r->delete();
        });

        $ret = $model->has_many(TestWidget::class);
        $this->assertNull($model->test_widgets);
    }

    public function test_has_many_delete_some()
    {
        $r = Relationship::one_to_many(TestWidget::class, TestVersion::class);

        $v1 = new TestVersion(array('version' => '1.2.1'));
        $v1->save();

        $v2 = new TestVersion(array('version' => '2.0.1'));
        $v2->save();

        $v3 = new TestVersion(array('version' => '2.0.1'));
        $v3->save();

        $widget = new TestWidget(array(
            'color' => 'fuchsia'
        ));
        $widget->save();

        $this->models[] = $v1;
        $this->models[] = $v2;
        $this->models[] = $v3;
        $this->models[] = $widget;

        $i1 = $r->between($widget, $v1);
        $i2 = $r->between($widget, $v2);
        $i3 = $r->between($widget, $v3);

        // Save
        $i1->save();
        $i2->save();
        $i3->save();

        $versions = array($v1->id => $v1, $v2->id => $v2, $v3->id => $v3);

        $ret = $widget->has_many(TestVersion::class);
        $this->assertIsArray($ret);
        $this->assertNotNull($widget->test_versions);
        $this->assertTrue(array_key_exists($widget->id, $ret));
        $this->assertIsArray($ret[$widget->id]);
        $this->assertEquals(3, count($ret[$widget->id]));
        foreach ($versions as $version_id => $version) {
            $this->assertTrue(
                array_key_exists($version_id, $ret[$widget->id]),
                sprintf("Version with id '%s' not found among members of the relationship.", $version_id)
            );
        }

        first(array_values($ret[$widget->id]))->delete();

        // The relationship has lost one member
        $ret = $widget->has_many(TestVersion::class);
        $this->assertIsArray($ret);
        $this->assertTrue(array_key_exists($widget->id, $ret));
        $this->assertIsArray($ret[$widget->id]);
        $this->assertEquals(2, count($ret[$widget->id]));
        foreach ($ret[$widget->id] as $version_id => $r) {
            $this->assertTrue(
                array_key_exists($version_id, $versions),
                sprintf("Unexpected version with id '%s' found among members of the relationship.", $version_id)
            );
        }
    }

    public function test_has_many_delete_all()
    {
        $r = Relationship::one_to_many(TestWidget::class, TestVersion::class);

        $v1 = new TestVersion(array('version' => '1.2.1'));
        $v1->save();

        $v2 = new TestVersion(array('version' => '2.0.1'));
        $v2->save();

        $widget = new TestWidget(array(
            'color' => 'fuchsia'
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

        $versions = array($v1, $v2);

        $ret = $widget->has_many(TestVersion::class);
        $this->assertIsArray($ret);
        $this->assertNotNull($widget->test_versions);
        $this->assertTrue(array_key_exists($widget->id, $ret));
        $this->assertIsArray($ret[$widget->id]);
        $this->assertEquals(2, count($ret[$widget->id]));
        foreach ($versions as $version) {
            $this->assertTrue(array_key_exists($version->id, $ret[$widget->id]));
        }

        foreach ($ret[$widget->id] as $r) {
            $r->delete();
        }
        // The relationship has been eliminated entirely
        $this->assertFalse($widget->has_many(TestVersion::class));
        // Side effect, the member should be unset
        $this->assertNull($widget->test_versions);
    }
}
?>
