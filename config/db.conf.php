<?php
	/**
	 *	Project EmeRails - Codename Ocarina
	 *
	 *	Copyright (c) 2008, 2015 Claudio Procida
	 *	http://www.emeraldion.it
	 *
	 */

switch ($_ENV['php_env'])
{
	case 'test':
		define("DB_ADAPTER", "mysqli");
		define("DB_USER", "root");
		define("DB_PASS", "root");
		define("DB_NAME", "emerails_test");
		define("DB_HOST", "localhost");
		break;
	case 'travisci':
		define("DB_ADAPTER", "mysqli");
		define("DB_USER", "root");
		define("DB_PASS", "");
		define("DB_NAME", "emerails_test");
		define("DB_HOST", "127.0.0.1");
		break;
	case 'prod':
	default:
		define("DB_ADAPTER", "mysql");
		define("DB_USER", "root");
		define("DB_PASS", "root");
		define("DB_NAME", "emeraldion.it");
		define("DB_HOST", "localhost");
}
define("DB_DEBUG", FALSE);

?>
