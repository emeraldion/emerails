<!DOCTYPE html>
<html>
	<head>
		<title><?php print $this->title; ?></title>
		<meta name="generator" content="EmeRails" />
		<link rel="icon" href="/assets/images/favicon.png" type="image/png" />
		<link rel="stylesheet" type="text/css" href="/assets/styles/styles.css" />
	</head>
	<body>
		<div id="main_content">
<?php
	print $this->content_for_layout;
?>
		</div>
		<div id="footer">
			<a href="http://emerails.emeraldion.it">EmeRails</a> &copy; 2008-2017 Claudio Procida &mdash; Emeraldion Lodge
		</div>
	</body>
</html>
