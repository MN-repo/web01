<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

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
<html xmlns="http://www.w3.org/1999/xhtml"
	xml:lang="en" >
<head>
<title>JMP</title>
</head>
<body>
<p>
<?php

include '../../../../settings-jmp.php';

$redis = new Redis();
$redis->pconnect($redis_host, $redis_port);
if (!empty($redis_auth)) {
	# TODO: check return value to confirm login succeeded
	$redis->auth($redis_auth);
}
$jmpnum = $redis->get('reg-num_vjmp-'.$_GET['sid']);

if (empty($_GET['sid'])) {
?>
Session ID empty.  Please <a href="../#support">contact support</a> to change or
setup your call forwarding options.
<?php

} elseif (!$jmpnum) {
?>
It looks like you haven't registered a JMP number yet or registered one a while
ago.  To setup call forwarding options for a JMP number, please
<a href="../">signup for one</a> or <a href="../#support">contact support</a>.
<?php

} elseif (empty($_GET['fwdphone'])) {
?>
</p>

<h2>Your JMP number is <?php echo $jmpnum ?></h2>

<p>
No forwarding phone number was entered, which means that all calls to your JMP
number will be sent to voicemail unless you've signed into your JMP SIP account
(press Back for the login details).  In particular, callers will hear "You have
reached the voicemail of a user of <a href="https://jmp.chat/">JMP.chat</a>.
Please send a text message, or leave a message after the tone."  If they leave a
voicemail, you will receive it via text message, both as an audio file, and as
transcribed text.
</p>

<p>
If you would prefer to enter a phone number that will be called when your JMP
number is called instead, then please press Back and enter a phone number.
</p>

<p>
Otherwise, you're done!  The easiest way to begin using JMP is by texting your
JMP number (<?php echo $jmpnum ?>) from another phone.  Alternatively, you can
start sending text messages using
<a href="../#sending">the instructions in the FAQ</a>.
<?php

} else {
	# first, normalize the fwdphone

	$clean_fwdphone = '';

	# see if it's a likely SIP address
	if (strpos($_GET['fwdphone'], '@') !== FALSE) {
		$clean_fwdphone = 'sip:'.$_GET['fwdphone'];
	} else {
		$clean_fwdphone = preg_replace('/[^0-9]/','',$_GET['fwdphone']);

		if (strlen($clean_fwdphone) == 11 && $clean_fwdphone[0] == '1'){
			$clean_fwdphone = substr($clean_fwdphone, 1);
		}

		if (strlen($clean_fwdphone) == 15 &&
			substr($clean_fwdphone, 0, 7) == '8835100') {

			$clean_fwdphone =
				'sip:'.$clean_fwdphone.'@sip.inum.net';
		} else if (strlen($clean_fwdphone) != 10) {
			# for E.164 numbers, we only currently support iNum/NANP
			$clean_fwdphone = '';
		} else {
			$clean_fwdphone = '+1'.$clean_fwdphone;
		}
	}

	if (!empty($clean_fwdphone)) {
		$hitsKey = 'reg-phn_hits-'.$clean_fwdphone;
		$hitCount = $redis->incr($hitsKey);

		# if more than 10 hits, do NOT allow verification to occur
		if ($hitCount > 10) {
			# if expiry not set yet then set expiry to 10 minutes
			if ($redis->ttl($hitsKey) < 0) {
				$redis->expire($hitsKey, 600);
			}

?>
Too many verification attempts.  Please refresh this page in about 10 minutes or
<a href="../#support">contact support</a>.
<?php
	# TODO: below should be indented by another tab, but leave as-is for now
	} else {
		# 4 bytes is based on Gmail verification code - 9 base-10 digits
		# might have to throw this away, but easier/safer than not doing
		# TODO: check $crypto_strong param - our system ok so can defer
		$pcodeBytes = openssl_random_pseudo_bytes(4);
		$pcode = bin2hex($pcodeBytes);

		$pcodeKey = 'reg-pcode-'.$clean_fwdphone;
		if ($redis->setNx($pcodeKey, $pcode)) {
			$redis->expire($pcodeKey, $key_ttl_seconds);
		}

		# it's important this is last so num expires after pcode expires
		# blow away the existing num - if user wants to change, let them
		$phoneKey = 'reg-phn_maybe-'.$_GET['sid'];
		$redis->setEx($phoneKey, $key_ttl_seconds, $clean_fwdphone);

		$clean_sid = preg_replace('/[^0-9a-f]/', '', $_GET['sid']);

		$jid = $redis->get('reg-jid_good-'.$_GET['sid']);
		if (!$jid) {
?>
No Jabber ID (JID) found for this session ID (sid).  Either find a sid that has
an associated JID or (likely easier) <a href="../#support">contact support</a>.
<?php
		} else {
			# call the fwdphone; jmp-fwdcalls will deliver the pcode
			$callParams = array(
				'tag'		=> $jid,  # for creds lookup
				'from'		=> $support_number,
				'to'		=> $clean_fwdphone,
				'callbackUrl'	=> $fwdcalls_url
			);
			$options = array('http' => array(
			'header'   => "Content-type: application/json\r\n",
			'method'   => 'POST',
			'content'  => json_encode($callParams)
			));

			$context = stream_context_create($options);
			$result = file_get_contents("https://$tuser:$token".
				'@api.catapult.inetwork.com/v1/users/'.
				"$user/calls", false, $context);
			if ($result === FALSE) {
?>
There was an error calling your number to deliver the code.  Please <a href=
"../register5/?sid=<?php
	echo $clean_sid;
?>&amp;fwdphone=<?php
	echo urlencode($_GET['fwdphone']);
?>">click here</a> or press Reload to try again or press Back to change your
forwarding number.
<?php
		        } else {
?>
</p>

<h2>Your JMP number is <?php echo $jmpnum ?></h2>

<p>
Please enter the verification code that was just delivered via phone call to
your forwarding number (<?php echo $clean_fwdphone ?>):
</p>

<form action="../register6/">
<p>
<input type="hidden" name="sid" value="<?php echo $clean_sid ?>" />
Code: <input type="text" name="pcode" /> <input type="submit" value="Submit" />
</p>
</form>

<p>
If you have not yet received the verification code, please <a href=
"../register5/?sid=<?php
	echo $clean_sid;
?>&amp;fwdphone=<?php
	echo urlencode($_GET['fwdphone']);
?>">click here</a> or press Reload to try again or press Back to change your
forwarding number.
<?php
			}
		}
	}
	# TODO: above should be indented by another tab, but leave as-is for now
	} else {
	echo "Forwarding number provided (".htmlentities($_GET['fwdphone']);
?>
) is not a <a href="https://en.wikipedia.org/wiki/North_American_Numbering_Plan"
>NANP</a> phone number (ie. +1 800 622 6232 or (800) 622-6232 - any format is
acceptable), a SIP URI (ie. sip_user@example.com), or an iNum (ie. +883 510 000
000 094).  Please press Back and try a different number or
<a href="../#support">contact support</a>.
<?php
	}
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
