<!DOCTYPE html>

<!--
  Copyright (C) 2017  Denver Gingerich <denver@ossguy.com>

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
<?php @include_once __DIR__ . '/../vendor/go.php'; ?>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>JMP - JIDs for Messaging with Phones - Porting</title>
<style type="text/css">
	dt { font-weight: bold }
	dd { margin-top: 5px; margin-bottom: 10px }
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
<div style="text-align:center;">
<a href="../"><img src="../static/jmp_beta.png" alt="JMP - beta" /></a>
<h3>Transfer request submitted!</h3>
</div>

<?php

$id_text = '';
if (!empty($_GET['jmp-number'])) {
	$id_text = 'temporary JMP number '.
		htmlentities($_GET['jmp-number']);
} elseif (!empty($_GET['jmp-jid'])) {
	$id_text = 'Jabber ID (JID) '.
		htmlentities($_GET['jmp-jid']);
} else {
	$id_text = 'unknown identifier';
}

$signature_date = date('m/d/Y');

include '../../../../settings-jmp.php';
mail($port_in_receiver_email, 'port '.htmlentities($_GET['port_number']).
	' to JMP', "convert command:\n\n".
	"date; time convert -background transparent -fill black -font \\\n".
		"URW-Chancery-L-Medium-Italic -pointsize 36 \\\n".
		'"label:'.htmlentities($_GET['port_fname']).'" '."\\\n".
		'psig'.preg_replace('/[^0-9]/', '', $signature_date).
		'-'.preg_replace('/[^0-9]/', '', $_GET['port_number']).
		'.png; echo $?; date'.
	"\n".
	"\n".
	'URL: '.$porting_form_url.'?jmp-number='.urlencode($_GET['jmp-number']).
		'&jmp-jid='.urlencode($_GET['jmp-jid']).
		'&port_number='.urlencode($_GET['port_number']).
		'&port_carrier='.urlencode($_GET['port_carrier']).
		'&port_fname='.urlencode($_GET['port_fname']).
		'&port_street='.urlencode($_GET['port_street']).
		'&port_city='.urlencode($_GET['port_city']).
		'&port_account='.urlencode($_GET['port_account']).
		'&port_pin='.urlencode($_GET['port_pin']).
		'&port_sdate='.$signature_date.
	"\n".
	"\n".
	"\n".
	'== critical details to include with port request =='.
	"\n".
	'account number:            '.htmlentities($_GET['port_account'])."\n".
	'PIN:                       '.htmlentities($_GET['port_pin'])."\n".
	"\n".
	"\n".
	"\n".
	'== purely informational additional details =='.
	"\n".
	'phone number to transfer:  '.htmlentities($_GET['port_number'])."\n".
	'name of carrier:           '.htmlentities($_GET['port_carrier'])."\n".
	'name on the account:       '.htmlentities($_GET['port_fname'])."\n".
	'billing street name/num:   '.htmlentities($_GET['port_street'])."\n".
	'billing city/region/code:  '.htmlentities($_GET['port_city'])."\n".
	'signature date:            '.$signature_date."\n".
	"\n".
	'JMP number:                '.htmlentities($_GET['jmp-number'])."\n".
	'JMP JID:                   '.htmlentities($_GET['jmp-jid'])."\n".
	'ID text:                   '.$id_text."\n");
?>

<p>
We've received your number transfer request for the JMP account with
<?php echo $id_text ?>.  We will contact you at your temporary JMP number within
the next week to let you know which date your number will transfer into JMP.
</p>

<p>
Please review the information below to confirm it is correct; if any details
need changing, press Back, change the value, and re-submit - any changes made
within 10 minutes of your original request will update the request.  Thanks for
using JMP!
</p>



<table style=
"margin-left:auto;margin-right:auto;border-spacing:1rem 0rem;"
>
<tr><td>

phone number to transfer

</td><td>

<p>
<?php echo htmlentities($_GET['port_number']) ?>
</p>

</td></tr>
<tr><td>

name of carrier

</td><td>

<p>
<?php echo htmlentities($_GET['port_carrier']) ?>
</p>

</td></tr>
<tr><td>

name on the account

</td><td>

<p>
<?php echo htmlentities($_GET['port_fname']) ?>
</p>

</td></tr>
<tr><td>

billing street name/number

</td><td>

<p>
<?php echo htmlentities($_GET['port_street']) ?>
</p>

</td></tr>
<tr><td>

billing city/region/postcode

</td><td>

<p>
<?php echo htmlentities($_GET['port_city']) ?>
</p>

</td></tr>
<tr><td>

account number

</td><td>

<p>
<?php echo htmlentities($_GET['port_account']) ?>
</p>

</td></tr>
<tr><td>

PIN

</td><td>

<p>
<?php echo htmlentities($_GET['port_pin']) ?>
</p>

</td></tr>
</table>

<hr />

<p>
Copyright &copy; 2017 <a href="https://ossguy.com/">Denver Gingerich</a> and
others.  jmp-register is licensed under AGPLv3+.
You can download the Complete Corresponding Source code <a
href="https://gitlab.com/ossguy/jmp-register">here</a>.
</p>
</body>
</html>
