<?php
	/**
	 *	Project EmeRails - Codename Ocarina
	 *
	 *	Copyright (c) 2008, 2015 Claudio Procida
	 *	http://www.emeraldion.it
	 *
	 */

if ($_ENV['php_env'] == 'test')
{
	define("DB_ADAPTER", "mysql");
	define("DB_USER", "root");
	define("DB_PASS", "root");
	define("DB_NAME", "emerails_test");
	define("DB_HOST", "localhost");
}
else
{
	define("DB_ADAPTER", "mysql");
	define("DB_USER", "root");
	define("DB_PASS", "root");
	define("DB_NAME", "emeraldion.it");
	define("DB_HOST", "localhost");
}
define("DB_DEBUG", FALSE);

?>
