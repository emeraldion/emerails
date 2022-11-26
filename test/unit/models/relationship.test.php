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

    public function test_save_many_to_many()
    {
        $r = Relationship::many_to_many(TestGroup::class, TestModel::class);

        // Config::set('DB_DEBUG', true);
        $model = new TestModel();
        $model->save();

        $group = new TestGroup();
        $group->save();
        // Config::set('DB_DEBUG', false);

        $this->models[] = $model;
        $this->models[] = $group;

        // These are both valid
        $instance = $r->between($group, $model);
        $instance = $r->between($model, $group);

        // But also (TBD):
        // $model->in_many_to_many_with($group);
        // $group->in_many_to_many_with($model);

        // Save
        $instance->save();

        $this->models[] = $instance;

        // Config::set('DB_DEBUG', true);
        $model->has_and_belongs_to_many(TestGroup::class);
        // Config::set('DB_DEBUG', false);
        $this->assertNotNull($model->test_groups);
        $this->assertEquals(1, count($model->test_groups));
        $this->assertEquals($group->name, $model->test_groups[0]->name);

        // Config::set('DB_DEBUG', true);
        $group->has_and_belongs_to_many(TestModel::class);
        // Config::set('DB_DEBUG', false);
        $this->assertNotNull($group->test_models);
        $this->assertEquals(1, count($group->test_models));
        $this->assertEquals($model->name, $group->test_models[0]->name);
    }
}
?>
