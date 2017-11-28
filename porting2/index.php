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
<h3>Bring your own number to JMP</h3>
</div>
<p>

<?php

$jid = '';
$clean_jmpnum = '';
$print_success = FALSE;

if (empty($_GET['jmpnum']) && empty($_GET['jid'])) {
?>
Please press Back and enter either a Jabber ID (JID) or JMP number to continue.
<?php
} else {
# TODO: below should be indented by another tab, but leave as-is for now

include '../../../../settings-jmp.php';

$redis = new Redis();
$redis->pconnect($redis_host, $redis_port);
if (!empty($redis_auth)) {
	# TODO: check return value to confirm login succeeded
	$redis->auth($redis_auth);
}

if (empty($_GET['jmpnum'])) {
	# trim and strip the resourcepart - we only accept bare JIDs
	$jid = strtolower(explode('/', trim($_GET['jid']), 2)[0]);

	if (strpos($jid, ' ') !== FALSE ||
		strpos($jid, '"') !== FALSE) {
?>
The JID that was entered (<?php echo htmlentities($jid)?>) contains at least one
space or quotation mark (").  Spaces and quotation marks are not allowed in bare
JIDs - please press Back and enter a valid JID or use your JMP number instead.
<?php
	} else {
		# TODO: XEP-0106 Sec 4.3 compliance; won't work with pre-escaped
		$ej_search  = array('\\',  ' ',   '"',   '&',   "'",   '/',
			':',   '<',   '>',   '@');
                $ej_replace = array('\5c', '\20', '\22', '\26', '\27', '\2f',
			'\3a', '\3c', '\3e', '\40');
		$cheo_jid = str_replace($ej_search, $ej_replace, $jid).'@'.
			$cheogram_jid;

		if ($redis->exists('catapult_cred-'.$cheo_jid)) {
			$print_success = TRUE;
?>
You've chosen to transfer in a number to the JMP account with Jabber ID (JID)
"<?php echo htmlentities($jid) ?>".  If that JID looks correct, then you can
continue with the transfer process.  Otherwise press Back to use a different JID
or JMP number.
</p>
<p>
<?php
		} else {
?>
It doesn't look like you're a JMP user yet (given your Jabber ID (JID),
"<?php echo htmlentities($jid) ?>").  Please <a href="../#signup">signup for a
temporary JMP number</a> - you can return here and transfer in your existing
non-JMP number after that.
<?php
		}
	}
} elseif (empty($_GET['jid'])) {
	# first, normalize the JMP numger
	$clean_jmpnum = preg_replace('/[^0-9]/', '', $_GET['jmpnum']);

	if (strlen($clean_jmpnum) == 11 && $clean_jmpnum[0] == '1') {
		$clean_jmpnum = substr($clean_jmpnum, 1);
	}

	if (strlen($clean_jmpnum) != 10) {
?>
The number you entered (<?php echo htmlentities($_GET['jmpnum']) ?>) doesn't
look like a JMP number, as it doesn't appear to be a
<a href="https://en.wikipedia.org/wiki/North_American_Numbering_Plan">NANP</a>
phone number (i.e. +1 800 622 6232 or (800) 622-6232).  Please press Back and
enter a JMP number or use your Jabber ID (JID) instead.
<?php
	} else {
		$clean_jmpnum = '+1'.$clean_jmpnum;

		if ($redis->exists('catapult_jid-'.$clean_jmpnum)) {
			$print_success = TRUE;
?>
To confirm, you'd like to transfer in a number to the JMP account with temporary
JMP number <?php echo $clean_jmpnum ?> (it will be replaced by the number you
are transferring in).  If that JMP number looks correct, then you can continue
with the transfer.  Otherwise, press Back to use a different JID or JMP number.
</p>
<p>
<?php
		} else {
?>
The number you entered (<?php echo $clean_jmpnum ?>) doesn't appear to be a JMP
number.  If you'd like to transfer in your existing phone number to use as your
JMP number, please <a href="../#signup">signup for a temporary JMP number</a>
first - you can return here and transfer in your existing non-JMP number after
that.
<?php
		}
	}
} else {
?>
Please press Back and enter just one of Jabber ID (JID) or JMP number.
<?php
}
# TODO: above should be indented by another tab, but leave as-is for now
}

if ($print_success) {
?>

Once you've submitted the details for the number you'd like to bring to JMP
below, we will begin the transfer process.  This normally takes 1-2 weeks during
which both your existing number and your temporary JMP number will continue to
work as they do now.
</p>

<p>
Within a day or two of submitting this form, we will know which date the
transfer will take place, and we will message your temporary JMP number with
this information.  Note that there is normally a period of 10-30 minutes at the
time of transfer where incoming calls and messages will not be delivered to the
number you are transferring in.  After that time, all calls and messages will be
handled by your JMP account, and your temporary JMP number will no longer
receive calls or messages.
</p>

<p>
If this all seems fine to you, then please enter the information for the number
you'd like to transfer into JMP:
</p>

<form action="../porting3/">
<input type="hidden" name="jmp-number" value ="<?php echo $clean_jmpnum ?>" />
<input type="hidden" name="jmp-jid" value ="<?php echo $jid ?>" />

<table style="margin-left:auto;margin-right:auto;border-spacing:1rem 0rem;">
<tr><td>

phone number (i.e. +1 416 555 1212)

</td><td>

<p>
<input type="tel" name="port_number" />
</p>

</td></tr>
<tr><td>

name of carrier (i.e. T-Mobile, Rogers, etc.)

</td><td>

<p>
<input type="text" name="port_carrier" />
</p>

</td></tr>
<tr><td>

name on the account (i.e. Jane Doe)

</td><td>

<p>
<input type="text" name="port_fname" />
</p>

</td></tr>
<tr><td>

billing street name/number (i.e. 1234 Dyer Ave Apt 321)

</td><td>

<p>
<input type="text" name="port_street" />
</p>

</td></tr>
<tr><td>

billing city/region/postcode (i.e. New York, NY 10199)

</td><td>

<p>
<input type="text" name="port_city" />
</p>

</td></tr>
<tr><td>

account number (required unless carrier is VoIP)

</td><td>

<p>
<input type="text" name="port_account" />
</p>

</td></tr>
<tr><td>

PIN (required unless your carrier has none)

</td><td>

<p>
<input type="text" name="port_pin" />
</p>

</td></tr>
</table>

<p style="text-align:center;">
<input type="submit" value="Submit" />
</p>

</form>

<p>
<?php
}
?>
</p>

<hr />

<p>
Copyright &copy; 2017 <a href="https://ossguy.com/">Denver Gingerich</a> and
others.  jmp-register is licensed under AGPLv3+.
You can download the Complete Corresponding Source code <a
href="https://gitlab.com/ossguy/jmp-register">here</a>.
</p>
</body>
</html>
