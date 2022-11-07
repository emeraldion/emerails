<?php
/**
 *	Project EmeRails - Codename Ocarina
 *
 *	Copyright (c) 2008, 2017 Claudio Procida
 *	http://www.emeraldion.it
 *
 * @format
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/include/common.inc.php';
require_once __DIR__ . '/helpers/application_helper.php';
require_once __DIR__ . '/helpers/http.php';

if (isset($_REQUEST['controller']) && !empty($_REQUEST['controller'])) {
    // Include controller class
    $controller_file = __DIR__ . "/controllers/{$_REQUEST['controller']}_controller.php";

    if (!file_exists($controller_file)) {
        HTTP::error(404);
    }
    require $controller_file;

    $main_controller_class = joined_lower_to_camel_case($_REQUEST['controller']) . 'Controller';

    // Instantiate main controller
    $main_controller = new $main_controller_class();

    // Request rendering of the page
    // (If action didn't already do it before)
    $main_controller->render_page();
} else {
    HTTP::error(500);
}
?>
