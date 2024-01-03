<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2024
 *
 * @format
 */

$this->set_title('404 - Not Found'); ?>
<h1>404 - Not Found</h1>
<p>
	Uh-oh! The requested resource <strong><?php print $_SERVER[
     'REDIRECT_URL'
 ]; ?></strong> does not exist (yet?) on this server.
</p>