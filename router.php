<?php
	/**
	 *	Project EmeRails - Codename Ocarina
	 *
	 *	Copyright (c) 2008, 2015 Claudio Procida
	 *	http://www.emeraldion.it
	 *
	 */
	
	require_once(dirname(__FILE__) . "/include/common.inc.php");
	require_once(dirname(__FILE__) . "/helpers/application_helper.php");
	require_once(dirname(__FILE__) . "/helpers/http.php");
	
	define('APPLICATION_ROOT', '/');
	
	if (isset($_REQUEST['controller']) && !empty($_REQUEST['controller']))
	{
		// Include controller class
		$controller_file = dirname(__FILE__) . "/controllers/{$_REQUEST['controller']}_controller.php";
		
		if (!file_exists($controller_file))
		{
			HTTP::error(404);
		}
		require($controller_file);
		
		// Instantiate main controller
		$main_controller = eval("return new " . ucwords($_REQUEST['controller']) . "Controller();");
		
		// Request rendering of the page
		// (If action didn't already do it before)
		$main_controller->render_page();
	}
	else
	{
		HTTP::error(500);
	}
?>