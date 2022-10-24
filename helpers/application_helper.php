<?php
/**
 *	Project EmeRails - Codename Ocarina
 *
 *	Copyright (c) 2008, 2017 Claudio Procida
 *	http://www.emeraldion.it
 *
 */

require_once dirname(__FILE__) . "/../config/emerails.conf.php";
require_once dirname(__FILE__) . "/../include/db.inc.php";
require_once dirname(__FILE__) . "/../include/" . DB_ADAPTER . "_adapter.php";
require_once dirname(__FILE__) . "/cookie.php";
require_once dirname(__FILE__) . "/time.php";
require_once dirname(__FILE__) . "/localization.php";

error_reporting(E_ALL | E_STRICT);
session_start();

class ApplicationHelper
{
    // Put here functionality available to all controllers within the application
}

?>
