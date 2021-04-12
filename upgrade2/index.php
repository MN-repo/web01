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

	#upgrade4 {
		text-align: center;
		max-width: 25em;
		margin: auto;
	}

	#upgrade4 fieldset {
		margin-bottom: 1em;
	}

	#upgrade4 label {
		display: block;
	}
</style>
</head>
<body style="padding: 0 5%;">
<div style="text-align:center;">
<a href="../"><img src="../static/jmp_beta.png" alt="JMP - beta" /></a>
<h3>Upgrade to a paid JMP account</h3>
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

$bc_id = '';

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
		$bc_id = urlencode($jid);

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
You've chosen to upgrade the JMP account with Jabber ID (JID)
"<?php echo htmlentities($jid) ?>" to a paid JMP account.  If that JID looks
correct, then you can continue with payment.  Otherwise press Back to use a
different JID or JMP number.
</p>
<p>
<?php
		} else {
?>
It doesn't look like you're a JMP user yet (given your Jabber ID (JID),
"<?php echo htmlentities($jid) ?>").  Please feel free to <a href="../">signup
for JMP</a> - you can return here and upgrade to a paid account after that.
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
phone number (ie. +1 800 622 6232 or (800) 622-6232).  Please press Back and
enter a JMP number or use your Jabber ID (JID) instead.
<?php
	} else {
		$bc_id = '1'.$clean_jmpnum;
		$clean_jmpnum = '+1'.$clean_jmpnum;

		if ($jid = $redis->get('catapult_jid-'.$clean_jmpnum)) {
			$print_success = TRUE;
?>
To confirm, you'd like to upgrade the JMP account with JMP number
<?php echo $clean_jmpnum ?> to a paid JMP account.  If that JMP number looks
correct, then you can continue with payment.  Otherwise, press Back to use a
different JID or JMP number.
</p>
<p>
<?php
		} else {
?>
The number you entered (<?php echo $clean_jmpnum ?>) doesn't appear to be a JMP
number.  If you'd like to get a JMP number, please feel free to
<a href="../">signup for JMP</a> - you can return here and upgrade to a paid
account after that.
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

Once you've completed the payment process, you'll receive unlimited incoming and
outgoing text and picture messages, and 120 minutes of voice calls per
month.  If you'd like to use a payment method other than PayPal or
cryptocurrency, please <a
href="../#payment">contact us</a>.  Otherwise please choose one of these payment
options:
</p>

<table style=
"margin-left:auto;margin-right:auto;text-align:center;border-spacing:8rem 0rem;"
>
<tr><td style="vertical-align:top;">
<p>
annual subscription<br />
US$34.99/year<br />
(2.5% savings)
</p>
</td></tr>
<tr><td>

<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
<p style="text-align:center;">
<input name="return" value="<?php
echo $register_base_url;
?>/upgrade3/?jmp-number=<?php
echo urlencode($clean_jmpnum);
?>&amp;jmp-jid=<?php
echo urlencode($jid);
?>" type="hidden" />
<?php echo $paypal_tags_annual ?>
</p>
</form>

</td></tr>
</table>

<table style=
"margin-left:auto;margin-right:auto;text-align:center;border-spacing:8rem 0rem;"
>
<tr><td style="vertical-align:top;">
<p>
monthly subscription<br />
US$2.99/month
</p>
</td></tr>
<tr><td>

<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
<p style="text-align:center;">
<input name="return" value="<?php
echo $register_base_url;
?>/upgrade3/?jmp-number=<?php
echo urlencode($clean_jmpnum);
?>&amp;jmp-jid=<?php
echo urlencode($jid);
?>" type="hidden" />
<?php echo $paypal_tags_monthly ?>
</p>
</form>

</td></tr>
</table>

<p>
You can also pay for your JMP account in Bitcoin.  If you'd
prefer to pay with an anonymous cryptocurrency like Monero or most other
cryptocurrencies, you can use a service like <a
href="https://simpleswap.io/">SimpleSwap</a>, <a
href="https://www.morphtoken.com/">MorphToken</a>, <a
href="https://changenow.io/">ChangeNOW</a>, or <a
href="https://godex.io/">Godex</a>.
</p>

<?php
	$customer_id = $redis->get('jmp_customer_id-' . $jid);
	if ($customer_id) {
		$addresses = $redis->smembers('jmp_customer_btc_addresses-' . $customer_id);
	}
	if(!empty($addresses)) :
?>
<p>You may buy account credit by sending any amount of BTC to any of these
addresses
(note that conversions are done using the Sell price of
<a href="https://www.canadianbitcoins.com/">Canadian Bitcoins</a>,
with any applicable CAD-to-USD conversion applied, within 5 minutes of your
transaction receiving at least 3 confirmations):</p>
<ul>
<?php foreach($addresses as $address): ?>
<li><?php echo $address; ?></li>
<?php endforeach; ?>
</ul>
<?php
	else :
?>

<p>
Once you've started the payment process below, you have 3 hours to make your
payment.  If you're not able to make your payment within that time, you can
return here to try again.
</p>

<table style=
"margin-left:auto;margin-right:auto;text-align:center;border-spacing:8rem 0rem;"
>
<tr><td style="vertical-align:top;">
<p>JMP account credit (0.55 mBTC or more)</p>
</td></tr>
<tr><td>
<form method="get" action="../upgrade4/" id="upgrade4">
	<input type="hidden" name="bc_id" value="<?php echo $bc_id ?>" />
	<input type="hidden" name="amount_sat" value="55000" />
	<input type="hidden" name="currency" value="USD" />

	<button type="submit" style="border: 0px none transparent;">
		<img
			src="../static/pay_with_bitcoin-lukasz_adam.png"
			alt="Pay with Bitcoin icon, by Lukasz Adam" />
	</button>
</form>
</td></tr>
</table>
<?php endif; ?>

<p>
<?php
}
?>
</p>

<hr />

<p>
Copyright &copy; 2017, 2020 <a href="https://ossguy.com/">Denver Gingerich</a> and
others.  jmp-register is licensed under AGPLv3+.
You can download the Complete Corresponding Source code <a
href="https://gitlab.com/ossguy/jmp-register">here</a>.
</p>
</body>
</html>
