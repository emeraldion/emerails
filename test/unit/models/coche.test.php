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

class Car extends ActiveRecord
{
    protected $table_name = 'coches';
}

class Engine extends ActiveRecord
{
    protected $table_name = 'motores';
}

class CocheTest extends \PHPUnit\Framework\TestCase
{
    public function test_correct_member_names_by_class()
    {
        $car = new Car();
        $ret = $car->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $car->has_one(Engine::class);
        $this->assertIsObject($ret);

        $engine = new Engine();
        $ret = $engine->find_by_id(1);
        $this->assertTrue($ret);
        $ret = $engine->belongs_to(Car::class);
        $this->assertIsObject($ret);

        // These are what we want
        $this->assertNotNull($car->engine, "Expecting Car instance to have an 'engine' field but found null");
        $this->assertNotNull($car->engine->car, "Expecting Engine instance to have a 'car' field but found null");

        $this->assertNotNull($engine->car, "Expecting Engine instance to have a 'car' field but found null");
        $this->assertNotNull($engine->car->engine, "Expecting Car instance to have an 'engine' field but found null");
    }

    public function test_correct_member_names_by_table_name()
    {
        $car = new Car();
        $ret = $car->find_by_id(1);
        $this->assertTrue($ret);
        // Note this is logically flawed as the real table name is 'motores'
        $ret = $car->has_one('engines');
        $this->assertIsObject($ret);

        $engine = new Engine();
        $ret = $engine->find_by_id(1);
        $this->assertTrue($ret);
        // Note this is logically flawed as the real table name is 'coches'
        $ret = $engine->belongs_to('cars');
        $this->assertIsObject($ret);

        // These are what we want
        $this->assertNotNull($car->engine, "Expecting Car instance to have an 'engine' field but found null");
        $this->assertNotNull($car->engine->car, "Expecting Engine instance to have a 'car' field but found null");

        $this->assertNotNull($engine->car, "Expecting Engine instance to have a 'car' field but found null");
        $this->assertNotNull($engine->car->engine, "Expecting Car instance to have an 'engine' field but found null");
    }
}
?>
