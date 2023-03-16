<?php
/**
 * @format
 */

require_once __DIR__ . '/vendor/emeraldion/emerails/include/common.inc.php';

use Emeraldion\EmeRails\Config;

// This flag controls additional debugging information and verbose error messages.
// Set it to false once you've finished development and are happy with the results.
Config::set('DEV_MODE', default_to(getenv('EMERAILS_DEV_MODE'), true));
Config::set('ERROR_REPORTING', default_to(getenv('EMERAILS_ERROR_REPORTING'), true));
Config::set('APPLICATION_ROOT', default_to(getenv('EMERAILS_APPLICATION_ROOT'), '/'));
Config::set('OBJECT_POOL_ENABLED', default_to(getenv('EMERAILS_OBJECT_POOL_ENABLED'), 'false'));

?>
