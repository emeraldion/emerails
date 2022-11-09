<?php
/**
 *	Project EmeRails - Codename Ocarina
 *
 *	Copyright (c) 2008, 2017 Claudio Procida
 *	http://www.emeraldion.it
 *
 * @format
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/include/common.inc.php';
require_once __DIR__ . '/helpers/application_helper.php';

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Helpers\ApplicationHelper;
use Emeraldion\EmeRails\Helpers\HTTP;

error_reporting(E_ALL);

if (isset($_REQUEST['controller']) && !empty($_REQUEST['controller'])) {
    $main_controller_class =
        'Emeraldion\\EmeRails\\Controllers\\' . joined_lower_to_camel_case($_REQUEST['controller']) . 'Controller';

    if (!class_exists($main_controller_class)) {
        HTTP::error(404);
    }

    // Instantiate main controller
    $main_controller = new $main_controller_class();

    // Set main controller's base path
    $main_controller->set_base_path(__DIR__);

    // Request rendering of the page
    // (If action didn't already do it before)
    $main_controller->render_page();
} else {
    HTTP::error(500);
}
?>
