<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2026
 *
 * @format
 */

require_once __DIR__ . '/../vendor/emeraldion/emerails/include/common.inc.php';

use Emeraldion\EmeRails\Config;

// This flag controls additional debugging information and verbose error messages.
// Set it to false once you've finished development and are happy with the results.
Config::set('DEV_MODE', default_to(getenv('EMERAILS_DEV_MODE'), true));
Config::set('ERROR_REPORTING', default_to(getenv('EMERAILS_ERROR_REPORTING'), true));
Config::set('APPLICATION_ROOT', default_to(getenv('EMERAILS_APPLICATION_ROOT'), '/'));
Config::set('LANGUAGE_COOKIE', default_to(getenv('EMERAILS_LANGUAGE_COOKIE'), 'hl'));
Config::set('OBJECT_POOL_ENABLED', default_to(getenv('EMERAILS_OBJECT_POOL_ENABLED'), false));
Config::set('RENDER_DEBUG', default_to(getenv('EMERAILS_RENDER_DEBUG'), false));

// Since the introduction of method allow rules, you can specify which HTTP methods are allowed by controllers.
// By default, "dangerous" methods (PUT, POST, DELETE) are blocked by controllers.
//
// You can customize this config setting to tweak the default list of allowed methods, or set its value to '*'
// to restore the legacy behavior of allowing all methods unless explicitly blocked.
Config::set(
    'DEFAULT_ALLOWED_METHODS',
    default_to(getenv('EMERAILS_DEFAULT_ALLOWED_METHODS'), ['GET', 'HEAD', 'OPTIONS'])
);
