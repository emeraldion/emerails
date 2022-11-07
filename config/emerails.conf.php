<?php
/**
 *	Project EmeRails - Codename Ocarina
 *
 *	Copyright (c) 2008, 2017 Claudio Procida
 *	http://www.emeraldion.it
 *
 * @format
 */

require_once __DIR__ . '/../include/common.inc.php';
require_once __DIR__ . '/config.php';

Config::set('ERROR_REPORTING', default_to(getenv('EMERAILS_ERROR_REPORTING'), true));
Config::set('APPLICATION_ROOT', default_to(getenv('EMERAILS_APPLICATION_ROOT'), '/'));

?>
