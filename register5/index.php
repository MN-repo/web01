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

<html xmlns="http://www.w3.org/1999/xhtml"
	xml:lang="en" >
<head>
<title>JMP</title>
</head>
<body>
<p>
<?php

if (empty($_GET['fwdphone'])) {
?>
</p>

<h2>You've selected <?php echo htmlentities($_GET['number']) ?> as your JMP
number</h2>

<p>
No forwarding phone number was entered, which means that all calls to your JMP
number would receive a pre-recorded message saying "This phone number does not
receive voice calls; please send a text message instead" and the caller would
not be able to leave a voicemail.
</p>

<p>
If you would prefer to enter a phone number that will be called when your JMP
number is called instead, then please press Back and enter a phone number.
Otherwise, please <a href="../register6/?number=<?php
	echo urlencode($_GET['number']);
?>&amp;sid=<?php
	echo urlencode($_GET['sid']);
?>&amp;pcode=nofwdnum">click here</a> to continue.
<?php

} elseif (empty($_GET['number']) || empty($_GET['sid'])) {
?>
Session ID and/or number empty.  Please <a href="../">start again</a>.
<?php

# TODO: update "== 12" for when we support non-NANPA numbers
} elseif (strlen($_GET['number']) == 12 && $_GET['number'][0] == '+' &&
	is_numeric(substr($_GET['number'], 1))) {

	# first, normalize the fwdphone
	$clean_fwdphone = preg_replace('/[^0-9]/', '', $_GET['fwdphone']);

	if (strlen($clean_fwdphone) == 11 && $clean_fwdphone[0] == '1') {
		$clean_fwdphone = substr($clean_fwdphone, 1);
	}

	if (strlen($clean_fwdphone) == 10) {
		$clean_fwdphone = '+1'.$clean_fwdphone;

		include '../../../../settings-jmp.php';

		$redis = new Redis();
		$redis->pconnect($redis_host, $redis_port);
		if (!empty($redis_auth)) {
			# TODO: check return value to confirm login succeeded
			$redis->auth($redis_auth);
		}
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
<a href="../">start again</a>.
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
an associated JID or (likely easier) <a href="../">start again</a>.
<?php
		} else {
			# call the fwdphone; jmp-fwdcalls will deliver the pcode
			$options = array('http' => array(
			'header'   => "Content-type: application/json\r\n",
			'method'   => 'POST',
			'content'  => '{"tag":"'.$jid.'",'.  # for creds lookup
				'"from":"'.$support_number.'",'.
				'"to":"'.$clean_fwdphone.'",'.
				'"callbackUrl":"'.$fwdcalls_url.'"}'
			));

			$context = stream_context_create($options);
			$result = file_get_contents("https://$tuser:$token".
				'@api.catapult.inetwork.com/v1/users/'.
				"$user/calls", false, $context);
			if ($result === FALSE) {
?>
There was an error calling your number to deliver the code.  Please <a href=
"../register5/?number=<?php
	echo urlencode($_GET['number']);
?>&amp;sid=<?php
	echo $clean_sid;
?>&amp;fwdphone=<?php
	echo urlencode($clean_fwdphone);
?>">click here</a> to try again or press Back to change your forwarding number.
<?php
		        } else {
?>
</p>

<h2>You've selected <?php echo $_GET['number'] ?> as your JMP number</h2>

<p>
Please enter the verification code that was just delivered via phone call to
your forwarding number (<?php echo $clean_fwdphone ?>):
</p>

<form action="../register6/">
<p>
<input type="hidden" name="number" value="<?php echo $_GET['number'] ?>" />
<input type="hidden" name="sid" value="<?php echo $clean_sid ?>" />
Code: <input type="text" name="pcode" /> <input type="submit" value="Submit" />
</p>
</form>

<p>
If you have not yet received the verification code, please <a href=
"../register5/?number=<?php
	echo urlencode($_GET['number']);
?>&amp;sid=<?php
	echo $clean_sid;
?>&amp;fwdphone=<?php
	echo urlencode($clean_fwdphone);
?>">click here</a> to try again or press Back to change your forwarding number.
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
acceptable).  Please press Back and try a different number or
<a href="../">start again</a>.
<?php
	}
} else {
	echo htmlentities($_GET['number']);
?>
 is not an E.164 NANP number.  Please <a href="../">start again</a>.
<?php
}
?>
</p>
<hr />
<p>
Copyright &copy; 2017 Denver Gingerich.  jmp-register is licensed under AGPLv3+.
You can download the Complete Corresponding Source code <a
href="https://gitlab.com/ossguy/jmp-register">here</a>.
</p>
</body>
</html>
