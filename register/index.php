<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>JMP: Register a New Number</title>

		<link rel="stylesheet" type="text/css" href="../style.css" />
		<style type="text/css">
			body > a {
				display: block;
				float: left;
				width: calc(40% - 4em);
				min-width: 10em;
				vertical-align: top;
				clear: left;
				margin-top: 2em;
				font-size: 1.5em;
				text-align: center;
				text-decoration: none;
				padding: 1em;
			}

			body > a:nth-of-type(2n) {
				float: right;
				clear: right;
			}

			@media (max-width: 50rem) {
				body > a {
					width: calc(80% - 4em);
					float: none;
					margin: auto;
				}

				body > a:nth-of-type(2n) {
					float: none;
				}
			}

			@media (hover: hover) {
				body > a:hover {
					background-color: #ccc;
				}
			}

			a[href^="../getjid"] {
				background-image: url(../static/getjid.svg);
				background-repeat: no-repeat;
				background-size: 3em;
				background-position: 1em center;
				padding-left: 4em;
			}

			a[href^="../register2"] {
				background-image: url(../static/havejid.svg);
				background-repeat: no-repeat;
				background-size: 3em;
				background-position: 1em center;
				padding-left: 4em;
			}
		</style>
	</head>

	<body>
		<?php $safeNumber = htmlentities($_GET['number']); ?>
		<h1>You've selected <?php
			echo $safeNumber;
			echo ' (';
			echo htmlentities($_GET['city']);
		?>) as your JMP number</h1>

		<a href="../getjid/?number=<?php echo urlencode($_GET['number']); ?>&amp;city=<?php echo urlencode($_GET['city']); ?>">
			I need to sign up for a new Jabber ID to use for this number
		</a>

		<a href="../register2/?number=<?php echo urlencode($_GET['number']); ?>&amp;city=<?php echo urlencode($_GET['city']); ?>">
			I already have a Jabber ID I want to use for this number
		</a>

		<?php require dirname(__FILE__).'/../nav.php'; ?>
	</body>
</html>
