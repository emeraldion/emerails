<?php
/**
 * @format
 */

namespace Emeraldion\EmeRails;

use Emeraldion\EmeRails\Config;

require_once __DIR__ . '/../include/db.inc.php';
require_once __DIR__ . '/../include/' . Config::get('DB_ADAPTER') . '_adapter.php';

class Db extends \Db
{
}

?>
