<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2025
 *
 * @format
 */

require_once __DIR__ . '/../../utils.php';
require_once __DIR__ . '/../base_test.php';

use Emeraldion\EmeRails\Models\ActiveRecord;
use Emeraldion\EmeRails\Models\Relationship;

class Car extends ActiveRecord
{
    protected $table_name = 'coches';
}

class Engine extends ActiveRecord
{
    protected $table_name = 'motores';
}

class CocheTest extends UnitTestBase
{
    public function test_get_table_name()
    {
        $e = new Engine();

        $this->assertEquals('motores', $e->get_table_name());

        $c = new Car();

        $this->assertEquals('coches', $c->get_table_name());
    }

    public function test_get_relationship_table_half_name()
    {
        $e = new Engine();

        $this->assertEquals('motores', $e->get_relationship_table_half_name());

        $c = new Car();

        $this->assertEquals('coches', $c->get_relationship_table_half_name());
    }

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
