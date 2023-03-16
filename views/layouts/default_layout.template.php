<?php
/**
 * @format
 */
use Emeraldion\EmeRails\Config; ?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php print $this->title; ?></title>
		<meta name="generator" content="EmeRails" />
		<link rel="icon" href="<?php print Config::get('APPLICATION_ROOT'); ?>assets/images/favicon.png" type="image/png" />
		<link rel="stylesheet" type="text/css" href="<?php print Config::get('APPLICATION_ROOT'); ?>assets/styles/styles.css" />
	</head>
	<body>
		<main>
<?php print $this->content_for_layout; ?>
		</main>
		<footer>
			Proudly powered by <a href="https://emerails.emeraldion.it">EmeRails</a>
		</footer>
	</body>
</html>
