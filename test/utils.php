<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2023
 *
 * @format
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.conf.php';

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

class Athlete extends ActiveRecord
{
}

?>
