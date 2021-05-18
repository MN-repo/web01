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
<title>JMP: Pay for your account</title>
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
<body>
<h1><a href="../"><img src="../static/jmp_beta.png" alt="JMP - beta" /></a></h1>
<h1>Pay for your JMP account</h1>

<p>
You can pay for your JMP account using this page, either via credit card or Bitcoin.
Activating an account via credit card currently requires US$15.00 (5 months),
there will be options to top-up larger amounts later.
Bitcoin users can top-up their account by sending any amount of Bitcoin to the
addresses associated with their account at any time.
</p>

<p>
During the beta, JMP is $2.99 USD / month or $3.59 CAD / month,
billed out of the balance on your account.  Paid beta accounts get unlimited
incoming and outgoing text and picture messages, and 120 minutes of voice calls
per month.
</p>

<p>
If you'd like to use a payment method other than credit card or cryptocurrency,
please <a
href="../faq/#payment">contact us</a>.  Otherwise please enter your JMP number or
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

</body>
</html>
