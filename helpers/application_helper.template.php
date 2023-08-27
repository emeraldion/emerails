<?php
/**
 * @format
 */

require_once __DIR__ . '/../config/db.conf.php';
require_once __DIR__ . '/../config/emerails.conf.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Emeraldion\EmeRails\Db;
use Emeraldion\EmeRails\DbAdapters\MysqlAdapter;
use Emeraldion\EmeRails\DbAdapters\MysqliAdapter;
use Emeraldion\EmeRails\Helpers\JSLocalizationHelper;
use Emeraldion\EmeRails\Helpers\Localization;

Db::register_adapter(new MysqliAdapter(), MysqliAdapter::NAME);
Db::register_adapter(new MysqlAdapter(), MysqlAdapter::NAME);

ini_set('display_errors', 'On');
date_default_timezone_set('Europe/Rome');
error_reporting(E_ALL | E_STRICT);
session_start();

Localization::set_base_dir(__DIR__ . '/../');
JSLocalizationHelper::set_base_dir(__DIR__ . '/../');

function exception_error_handler($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    switch ($severity) {
        case E_ERROR:
            $severity = 'E_ERROR';
            break;
        case E_WARNING:
            $severity = 'E_WARNING';
            break;
        case E_NOTICE:
            $severity = 'E_NOTICE';
            break;
        case E_PARSE:
            $severity = 'E_PARSE';
            break;
        case E_STRICT:
            $severity = 'E_STRICT';
            break;
        case E_DEPRECATED:
            $severity = 'E_DEPRECATED';
            break;
    }
    $file = preg_replace('/' . addcslashes(dirname(__FILE__, 2), '/') . '/', htmlentities('<PROJECT_ROOT>'), $file);
    printf('<pre><code>[%s] %s %s:%d</code></pre>', $severity, $message, $file, $line);
    // throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler('exception_error_handler');

class ApplicationHelper
{
    // Put here functionality available to all controllers within the application
}

?>
