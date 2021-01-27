<!DOCTYPE html>

<!--
  Copyright (C) 2017, 2020  Denver Gingerich <denver@ossguy.com>

  This file is part of jmp-register.

  jmp-register is free software: you can redistribute it and/or modify it under
  the terms of the GNU Affero General Public License as published by the Free
  Software Foundation, either version 3 of the License, or (at your option) any
  later version.

  jmp-register is distributed in the hope that it will be useful, but WITHOUT
  ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
  FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
  details.

  You should have received a copy of the GNU Affero General Public License along
  with jmp-register.  If not, see <http://www.gnu.org/licenses/>.
-->

<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>JMP - JIDs for Messaging with Phones - Upgrade</title>
<link rel="stylesheet" type="text/css" href="../style.css" />
<style type="text/css">
	#signup_bottom {
		display: inline-block;
		border-radius: 0.5rem;
		background-color: #00a;
		color: white;
		padding: 1em;
		text-decoration-line: none;
	}
</style>
</head>
<body style="padding: 0 5%;">
<h1><a href="../"><img src="../static/jmp_beta.png" alt="JMP - beta" /></a></h1>
<h1>Upgrade to a paid JMP account</h1>

<p>
If you're happy with your <a href="../">JMP</a> free trial so far, you can
upgrade to a paid account at any time within the first 30 days.  There are
currently four options for paid accounts: US$2.99/month or US$34.99/year (a 2.5%
savings), or <abbr title="0.00055 Bitcoin">0.55 mBTC</abbr> for 1 year, <abbr
title="0.00155 Bitcoin">1.55 mBTC</abbr> for 3 years (a 6% savings).
</p>

<p>
Once you've completed the payment process, you'll receive unlimited incoming and
outgoing text and picture messages, and 120 minutes of voice calls per
month.
</p>

<p>
Note that unlimited text and picture messages will last for the duration of the
payment period that you choose.  After that, if JMP is still in beta (as it will
be until at least July 2021), your account will auto-renew with unlimited text
and picture messages.  If JMP is no longer in beta, then your account will be
automatically updated to a new account type with limited outgoing text and
picture messages, but will keep unlimited incoming text and picture messages.
There will be other account options at that time if you wish to keep the
unlimited outgoing text and picture messages.
</p>

<p>
If you'd like to use a payment method other than PayPal or cryptocurrency,
please <a
href="../#payment">contact us</a>.  Otherwise please enter your JMP number or
Jabber ID (JID) below:
</p>

<table style="margin-left:auto;margin-right:auto;text-align:center;">
<tr><td>
<p>JMP number:</p>
</td><td>
<form action="../upgrade2/">
<p><input type="text" name="jmpnum" /><input type="submit" value="Submit" /></p>
</form>
</td></tr>

<tr><td colspan="2"><em>OR</em></td></tr>

<tr><td>
<p>Jabber ID:</p>
</td><td>
<form action="../upgrade2/">
<p><input type="text" name="jid" /><input type="submit" value="Submit" /></p>
</form>
</td></tr>
</table>

<?php require dirname(__FILE__).'/../nav.php'; ?>

<hr />

<p>
Copyright &copy; 2017 <a href="https://ossguy.com/">Denver Gingerich</a> and
others.  jmp-register is licensed under AGPLv3+.
You can download the Complete Corresponding Source code <a
href="https://gitlab.com/ossguy/jmp-register">here</a>.
</p>

</body>
</html>
