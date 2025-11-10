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

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.conf.php';
require_once __DIR__ . '/../config/emerails.conf.php';

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Db;
use Emeraldion\EmeRails\DbAdapters\MysqlAdapter;
use Emeraldion\EmeRails\DbAdapters\MysqliAdapter;
use Emeraldion\EmeRails\Helpers\HTTP;
use Emeraldion\EmeRails\Helpers\JSLocalizationHelper;
use Emeraldion\EmeRails\Helpers\Localization;

Db::register_adapter(new MysqliAdapter(), MysqliAdapter::NAME);
Db::register_adapter(new MysqlAdapter(), MysqlAdapter::NAME);

ini_set('display_errors', 'On');
date_default_timezone_set('Europe/Rome');
error_reporting(E_ALL | E_STRICT);
session_start();

const BASE_DIR = __DIR__ . '/../';
Localization::set_base_dir(BASE_DIR);
JSLocalizationHelper::set_base_dir(BASE_DIR);

const ERROR_NAMES = [
    E_ERROR => 'E_ERROR',
    E_WARNING => 'E_WARNING',
    E_PARSE => 'E_PARSE',
    E_NOTICE => 'E_NOTICE',
    E_CORE_ERROR => 'E_CORE_ERROR',
    E_CORE_WARNING => 'E_CORE_WARNING',
    E_COMPILE_ERROR => 'E_COMPILE_ERROR',
    E_COMPILE_WARNING => 'E_COMPILE_WARNING',
    E_USER_ERROR => 'E_USER_ERROR',
    E_USER_WARNING => 'E_USER_WARNING',
    E_USER_NOTICE => 'E_USER_NOTICE',
    E_STRICT => 'E_STRICT',
    E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
    E_DEPRECATED => 'E_DEPRECATED',
    E_USER_DEPRECATED => 'E_USER_DEPRECATED',
    E_ALL => 'E_ALL'
];

// error handler function
function custom_error_handler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
    }

    // $errstr may need to be escaped:
    $safe_errstr = h($errstr);

    $error_message = sprintf("<strong>%s [%d]:</strong> %s\n", ERROR_NAMES[$errno], $errno, $safe_errstr);

    if (Config::get('ERROR_REPORTING')) {
        $_SESSION['errno'] = $errno;
        $_SESSION['errstr'] = $errstr;
        $_SESSION['error_message'] = $error_message;
        $_SESSION['debug_stacktrace'] = sanitize_stacktrace(
            var_export(array_slice(debug_backtrace(), 2), true),
            BASE_DIR,
            '<PROJECT_ROOT>'
        );
    }

    HTTP::error(500);

    /* Don't execute PHP internal error handler */
    return false;
}

// set to the user defined error handler
$old_error_handler = set_error_handler('custom_error_handler');

class ApplicationHelper
{
    // Put here functionality available to all controllers within the application
}
