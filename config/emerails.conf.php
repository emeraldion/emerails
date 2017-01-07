<?php
	/**
	 *	Project EmeRails - Codename Ocarina
	 *
	 *	Copyright (c) 2008, 2017 Claudio Procida
	 *	http://www.emeraldion.it
	 *
	 */

	require_once(dirname(__FILE__) . "/../include/common.inc.php");

	define('ERROR_REPORTING',  default_to(getenv('EMERAILS_ERROR_REPORTING'),  TRUE));
	define('APPLICATION_ROOT', default_to(getenv('EMERAILS_APPLICATION_ROOT'), '/'));
?>
