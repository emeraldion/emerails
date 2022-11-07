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

switch (getenv('php_env')) {
    case 'test':
        Config::set('DB_ADAPTER', default_to(getenv('DB_ADAPTER'), 'mysqli'));
        Config::set('DB_USER', default_to(getenv('DB_USER'), 'root'));
        Config::set('DB_PASS', default_to(getenv('DB_PASS'), 'root'));
        Config::set('DB_NAME', default_to(getenv('DB_NAME'), 'emerails_test'));
        Config::set('DB_HOST', default_to(getenv('DB_HOST'), 'localhost'));
        break;
    case 'travisci':
        Config::set('DB_ADAPTER', default_to(getenv('DB_ADAPTER'), 'mysqli'));
        Config::set('DB_USER', default_to(getenv('DB_USER'), 'root'));
        Config::set('DB_PASS', default_to(getenv('DB_PASS'), ''));
        Config::set('DB_NAME', default_to(getenv('DB_NAME'), 'emerails_test'));
        Config::set('DB_HOST', default_to(getenv('DB_HOST'), '127.0.0.1'));
        break;
    case 'prod':
    default:
        Config::set('DB_ADAPTER', default_to(getenv('DB_ADAPTER'), 'mysql'));
        Config::set('DB_USER', default_to(getenv('DB_USER'), 'root'));
        Config::set('DB_PASS', default_to(getenv('DB_PASS'), 'root'));
        Config::set('DB_NAME', default_to(getenv('DB_NAME'), 'emerails'));
        Config::set('DB_HOST', default_to(getenv('DB_HOST'), 'localhost'));
}
Config::set('DB_CHARSET', default_to(getenv('DB_CHARSET'), 'utf8mb4'));
Config::set('DB_DEBUG', default_to(getenv('DB_DEBUG'), false));

?>
