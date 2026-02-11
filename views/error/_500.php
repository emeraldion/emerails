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
?>
<h1><?php print l('error-_500-heading'); ?></h1>
<p>
	<?php print h(l('error-_500-blurb')); ?>
</p>

<?php if (Config::get('ERROR_REPORTING') && isset($_SESSION['error_message'])) { ?>
<h2><?php print l('error-_500-error-message-heading'); ?></h2>
<div class="error-message">
	<pre id="error-message-text"><?php
 printf(
     "%s\n%s\n--\n%s\n",
     @$_SESSION['error_message'],
     l('error-_500-error-message-stacktrace-heading'),
     @$_SESSION['debug_stacktrace']
 );

 unset($_SESSION['errno']);
 unset($_SESSION['errstr']);
 unset($_SESSION['debug_stacktrace']);
 unset($_SESSION['error_message']);
 ?></pre>
	<div class="error-actions">
		<button class="btn btn-flush" onclick="navigator.clipboard.writeText(document.querySelector('#error-message-text')?.textContent);">
			<?php print h(l('error-_500-error-message-stacktrace-copy-button-label')); ?>
		</button>
	</div>
</div>
<?php } ?>
