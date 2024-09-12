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
?>
<h1><?php print l('base-index-heading'); ?></h1>
<p>
	<?php print l('base-index-para-1'); ?>
</p>
<p>
	<?php printf(
     l('base-index-para-2-@2'),
     sprintf('<a href="https://emerails.emeraldion.it/tutorial.html">%s</a>', l('base-index-tutorial-link')),
     sprintf('<a href="https://emerails.emeraldion.it/docs.html">%s</a>', l('base-index-docs-link')),
     sprintf('<a href="https://emerails.emeraldion.it/discuss.html">%s</a>', l('base-index-discussion-group-link'))
 ); ?>
</p>
