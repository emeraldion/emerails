<?php
	/**
	 *	Project EmeRails - Codename Ocarina
	 *
	 *	Copyright (c) 2008, 2017 Claudio Procida
	 *	http://www.emeraldion.it
	 *
	 */

	require_once(dirname(__FILE__) . "/../include/common.inc.php");

	switch (getenv('php_env'))
	{
		case 'test':
			define('DB_ADAPTER', default_to(getenv('DB_ADAPTER'), 'mysqli'));
			define('DB_USER',    default_to(getenv('DB_USER'),    'root'));
			define('DB_PASS',    default_to(getenv('DB_PASS'),    'root'));
			define('DB_NAME',    default_to(getenv('DB_NAME'),    'emerails_test'));
			define('DB_HOST',    default_to(getenv('DB_HOST'),    'localhost'));
			break;
		case 'travisci':
			define('DB_ADAPTER', default_to(getenv('DB_ADAPTER'), 'mysqli'));
			define('DB_USER',    default_to(getenv('DB_USER'),    'root'));
			define('DB_PASS',    default_to(getenv('DB_PASS'),    ''));
			define('DB_NAME',    default_to(getenv('DB_NAME'),    'emerails_test'));
			define('DB_HOST',    default_to(getenv('DB_HOST'),    '127.0.0.1'));
			break;
		case 'prod':
		default:
			define('DB_ADAPTER', default_to(getenv('DB_ADAPTER'),  'mysql'));
			define('DB_USER',    default_to(getenv('DB_USER'),     'root'));
			define('DB_PASS',    default_to(getenv('DB_PASS'),     'root'));
			define('DB_NAME',    default_to(getenv('DB_NAME'),     'emerails'));
			define('DB_HOST',    default_to(getenv('DB_HOST'),     'localhost'));
	}
	define('DB_DEBUG', default_to(getenv('DB_DEBUG'), FALSE));

?>
