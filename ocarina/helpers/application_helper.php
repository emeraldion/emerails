<?php
	require_once("{$_SERVER['DOCUMENT_ROOT']}/include/db.inc.php");
	require_once("{$_SERVER['DOCUMENT_ROOT']}/helpers/cookie.php");
	require_once("{$_SERVER['DOCUMENT_ROOT']}/helpers/time.php");
	require_once("{$_SERVER['DOCUMENT_ROOT']}/helpers/localization.php");
	
	error_reporting(E_ALL | E_STRICT);
	session_start();

	class ApplicationHelper
	{
		// Put here functionality available to all controllers within the application
	}

?>