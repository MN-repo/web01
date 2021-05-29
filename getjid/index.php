<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>JMP: Get a Jabber ID</title>

		<link rel="stylesheet" type="text/css" href="../style.css" />
		<style type="text/css">
			body > section {
				margin-top: 3em;
			}

			a#chatterboxtown {
				font-size: 3em;
				display: block;
				text-align: center;
			}

			a > img {
				height: 5em;
			}

			body > section ul {
				padding: 0;
			}

			body > section ul li {
				padding: 0;
				list-style-type: none;
				display: inline-block;
			}

			body > section ul li a {
				display: inline-block;
				height: 5em;
			}

			a[href="https://gajim.org/"] {
				background: #000;
				border-radius: 0.5em;
				border: 0.13em solid #888;
				text-decoration: none;
				color: #fff;
			}

			a[href="https://gajim.org/"] span {
				font-size: 2em;
				padding-right: 0.5em;
				position: relative;
				top: -1em;
			}

			#continue a {
				font-size: 3em;
				text-decoration: none;
			}
		</style>
	</head>

	<body>
		<?php $safeNumber = htmlentities($_GET['number']); ?>
		<h1>Step 1: Set up a new Jabber ID for <?php echo $safeNumber; ?> (<?php echo $_GET['city']; ?>)</h1>

		<p>A Jabber ID is an account identifier similar to an email address.  It is what you will log into your Jabber client with and how you will send and receive text messages with your new phone number.  You can use a Jabber ID from any standards-compliant service with JMP.</p>

		<a
			id="chatterboxtown"
			href="https://chatterboxtown.us:5443/register/new/"
			target="_blank"
		>
			Get one from ChatterboxTown
		</a>
		<script type="text/javascript">
			document.querySelector("#chatterboxtown").href =
				"https://movim.chatterboxtown.us/?account";
		</script>

		<section>
			<h1>Step 2: Sign in with your new Jabber ID using a Jabber app</h1>

			<p>Any standards-compliant Jabber app on any platform will work.</p>

			<section>
				<h1>Mobile</h1>

				<ul>
					<li><a target="_blank" href="https://f-droid.org/app/eu.siacs.conversations"><img src="../static/fdroid.png" alt="Get it on F-Droid" /></a></li>
					<li><a target="_blank" href="https://play.google.com/store/apps/details?id=eu.siacs.conversations"><img src="../static/google_play.png" alt="Get it on Google Play" /></a></li>
					<li><a target="_blank" href="https://apps.apple.com/us/app/siskin-im/id1153516838"><img src="../static/appstore.svg" alt="Get it on the AppStore" /></a></li>
				</ul>
			</section>

			<section>
				<h1>Desktop / Laptop</h1>

				<ul>
					<li><a target="_blank" href="https://apps.apple.com/us/app/beagleim-by-tigase-inc/id1445349494"><img src="../static/mac_appstore.svg" alt="Get it on the Mac AppStore" /></a></li>
					<li><a target="_blank" href="https://www.microsoft.com/store/apps/9nw16x9jb5wv?ocid=badge"><img src="../static/microsoftstore.svg" alt="Get it from Microsoft" /></a></li>
					<li><a target="_blank" href="https://gajim.org/"><img src="../static/gajim-logo.png" alt="" /> <span>Gajim</span></a></li>
				</ul>
			</section>
		</section>

		<section id="continue">
			<h1>Step 3: Get your new number</h1>

			<a href="../register2/?number=<?php echo urlencode($_GET['number']); ?>&amp;city=<?php echo urlencode($_GET['city']); ?>">
				Continue ➡
			</a>
		</section>

		<?php require dirname(__FILE__).'/../nav.php'; ?>
	</body>
</html>
