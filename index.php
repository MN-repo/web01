<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>JMP: Your phone number on every device</title>

		<link rel="stylesheet" type="text/css" href="style.css" />
		<style type="text/css">
			h1 {
				text-align: center;
				margin-top: 0;
			}

			body > section {
				display: block;
				float: left;
				width: 40%;
				min-width: 15em;
				vertical-align: top;
				clear: left;
				margin-top: 2em;
			}

			body > section:before {
				content: "";
				background: no-repeat center;
				background-size: contain;
				display: block;
				height: 3em;
				margin-bottom: 0.25em;
			}

			body > section:nth-of-type(2n) {
				float: right;
				clear: right;
			}

			@media (max-width: 50rem) {
				body > section {
					width: 100%;
					max-width: 30em;
					float: none;
					margin: auto;
				}

				body > section:nth-of-type(2n) {
					float: none;
				}
			}

			#signup {
				float: none;
				clear: both;
				margin: 0 auto;
				padding-top: 2em;
				text-align: center;
			}

			#devices:before {
				background-image: url(static/devices.svg);
			}

			#multiple-numbers:before {
				background-image: url(static/numbers.svg);
			}

			#freedom:before {
				background-image: url(static/freedom.svg);
			}

			#share:before {
				background-image: url(static/share.svg);
			}

			iframe {
				display: block;
				margin: 0 auto;
				border: 0;
				width: 18rem;
				height: 14rem;
			}
		</style>

		<script type="text/javascript">
			if(
				window.location.hash &&
				!document.querySelector(window.location.hash)
			) {
				window.location = "/faq" + window.location.hash;
			}
		</script>
	</head>

	<body>
		<h1><img src="static/jmp_beta.png" alt="JMP" /></h1>

		<section id="devices">
			<h1>Your phone number on every device</h1>

			<p>JMP gives you a real phone number that is yours for calling and texting, including group and picture messages, that works from all your devices at once.  Because we use the Jabber and SIP open protocols, you can use any existing client even if we don't have an official recommendation for your device yet!</p>
		</section>

		<section id="multiple-numbers">
			<h1>Multiple phone numbers, one app</h1>

			<p>Get different number to give out to friends, to dates, to business contacts: whatever your needs, JMP helps you protect your privacy by keeping separate parts of your life, separate.</p>
		</section>

		<section id="freedom" >
			<h1>Free as in Freedom</h1>

			<p>You have the freedom to inspect <a href="https://soprani.ca">all software</a> used to provide JMP, or even submit modifications.</p>
		</section>

		<section id="share">
			<h1>Share one number with multiple people</h1>

			<p>JMP numbers can be shared with as many people as makes sense, either on rotation or all at once. Perfect for groups and small businesses who need to be reachable by text message.</p>
		</section>

		<section id="signup">
			<h1>Start by choosing a phone number to use</h1>

			<iframe src="register1/num_find.php"></iframe>

			<p>Or search <a href="register1">by area code</a> to find even more numbers.</p>

			<p>You can also <a href="porting1">bring your own number</a> to JMP if you like.</p>
		</section>

		<p class="warning">
			<b>Note:</b> While JMP does provide phone numbers and voice/SMS features, it does not provide 911, 112, 999 or other emergency services over voice or SMS.
		</p>

		<?php $at_root = true; ?>
		<?php require dirname(__FILE__).'/nav.php'; ?>
	</body>
</html>
