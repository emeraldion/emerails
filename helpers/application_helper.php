<?php
/**
 *	Project EmeRails - Codename Ocarina
 *
 *	Copyright (c) 2008, 2017 Claudio Procida
 *	http://www.emeraldion.it
 *
 * @format
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.conf.php';
require_once __DIR__ . '/../config/emerails.conf.php';

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Db;
use Emeraldion\EmeRails\DbAdapters\MysqlAdapter;
use Emeraldion\EmeRails\DbAdapters\MysqliAdapter;

error_reporting(E_ALL | E_STRICT);
session_start();

function exception_error_handler($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    printf('%s %s %s:%d', $severity, $message, $file, $line);
    // throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler('exception_error_handler');

class ApplicationHelper
{
    // Put here functionality available to all controllers within the application
}

?>
