<?php
/**
 *	Project EmeRails - Codename Ocarina
 *
 *	Copyright (c) 2008, 2017 Claudio Procida
 *	http://www.emeraldion.it
 *
 * @format
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/emerails.conf.php';
require_once __DIR__ . '/../include/db.inc.php';
require_once __DIR__ . '/../include/' . Config::get('DB_ADAPTER') . '_adapter.php';
require_once __DIR__ . '/cookie.php';
require_once __DIR__ . '/time.php';
require_once __DIR__ . '/localization.php';

error_reporting(E_ALL | E_STRICT);
session_start();

class ApplicationHelper
{
    // Put here functionality available to all controllers within the application
}

?>
