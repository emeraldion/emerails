<!DOCTYPE html>
<html>
	<head>
		<title><?php print $this->title; ?></title>
		<meta name="generator" content="EmeRails" />
		<link rel="icon" href="/assets/images/favicon.png" type="image/png" />
		<style type="text/css">
			html
			{
				padding: 0;
				margin: 0;
				height: 100%;
				background: #fff url("/assets/images/gradient_azure.png") repeat-x 0 0;
			}
			body
			{
				padding: 0;
				margin: 0;
				font-family: "Lucida Grande", "Trebuchet MS", sans-serif;
				height: 100%;
				background: transparent url("/assets/images/emerails_logo.png") no-repeat bottom right;
			}
			#main_content
			{
				width: 800px;
				padding: 50px 0 0;
				margin: auto;
			}
			h1
			{
				color: #333;
				padding-bottom: 0.25em;
				border-bottom: 1px solid #eee;
			}
			#footer
			{
				margin: 150px auto 0;
				text-align: center;
			}
			a:link,
			a:visited
			{
				color: #090;
			}
			a:hover,
			a:active
			{
				color: #f90;
			}
		</style>
		<script type="text/javascript">
		</script>
	</head>
	<body>
		<div id="main_content">
<?php
	print $this->content_for_layout;
?>
		</div>
		<div id="footer">
			<a href="http://emerails.emeraldion.it">EmeRails</a> &copy; 2008 Claudio Procida &mdash; Emeraldion Lodge
		</div>
	</body>
</html>
