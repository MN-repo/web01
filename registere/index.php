<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

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

<html xmlns="http://www.w3.org/1999/xhtml"
	xml:lang="en" >
<head>
<title>JMP</title>
<?php

if (empty($_GET['jcode'])) {
?>
</head>
<body>
<p>
Verification code not entered.  Please press Back and enter a verification code
or <a href="../">start again</a>.
<?php
} elseif (empty($_GET['number'])) {
?>
</head>
<body>
<p>
Number not entered.  Please <a href="../">start again</a>.
<?php
} elseif (empty($_GET['sid'])) {
?>
</head>
<body>
<p>
No session ID found.  Please <a href="../">start again</a>.
<?php
} else {
	include '../../../../settings-jmp.php';

	$redis = new Redis();
	$redis->pconnect($redis_host, $redis_port);
	if (!empty($redis_auth)) {
		# TODO: check return value to confirm login succeeded
		$redis->auth($redis_auth);
	}

	$jidMaybeKey = 'reg-jid_maybe-'.$_GET['sid'];
	$jid = $redis->get($jidMaybeKey);

	# TODO NOW: check for empty/null $jid

	$clean_sid = preg_replace('/[^0-9a-f]/', '', $_GET['sid']);
	$jcode = preg_replace('/[^0-9a-f]/', '', $_GET['jcode']);

	# send the registration request message: informational, not yet blocking
	$options = array('http' => array(
		'header'   => "Content-type: application/json\r\n",
		'method'   => 'POST',
		'content'  => '{"receiptRequested":"all",'.
			'"tag":"signup'.$jcode.$jid.' jmp-register",'.
			'"callbackUrl":"'.$fwdcalls_url.'",'.
			'"from":"'.$support_number.'",'.
			'"to":"'.$cheogram_did.'",'.
			# TODO NOW: update this to add link/reply code 2 approve
			'"text":"/msg '.$notify_pending_signup_jid.
			' At '.gmdate("Y-m-d H:i:s").'Z user with JID '.$jid.
			' requested registration - informational for now, but '.
			'a reply will be needed in the future."}'
	));

	$context = stream_context_create($options);
	$result = file_get_contents("https://$tuser:$token".
		'@api.catapult.inetwork.com/v1/users/'.
		"$user/messages", false, $context);
	if ($result === FALSE) {
?>
</head>
<body>
<p>
There was an error sending your registration request.  Please <a href=
"../registere/?number=<?php
	echo urlencode($_GET['number']);
?>&amp;sid=<?php
	echo $clean_sid;
?>&amp;jcode=<?php
	echo $jcode;
?>">click here</a> or press Reload to try again.
<?php
        } else {
?>
<meta http-equiv="refresh" content="3;url=../register4/?number=<?php
	echo urlencode($_GET['number']);
?>&sid=<?php
	echo $clean_sid;
?>&jcode=<?php
	echo $jcode;
?>" />
</head>
<body>

<h2>Processing registration...</h2>

<p>
If this page has been displayed for more than 5 seconds please <a href=
"../register4/?number=<?php
	echo urlencode($_GET['number']);
?>&amp;sid=<?php
	echo $clean_sid;
?>&amp;jcode=<?php
	echo $jcode;
?>">click here</a> to proceed.

<?php
	}
}
/*
# TODO: update "== 12" for when we support non-NANPA numbers
} elseif (strlen($_GET['number']) == 12 && $_GET['number'][0] == '+' &&
	is_numeric(substr($_GET['number'], 1))) {

	include '../../../../settings-jmp.php';

	$redis = new Redis();
	$redis->pconnect($redis_host, $redis_port);
	if (!empty($redis_auth)) {
		# TODO: check return value to confirm login succeeded
		$redis->auth($redis_auth);
	}

	$jidMaybeKey = 'reg-jid_maybe-'.$_GET['sid'];
	$jid = $redis->get($jidMaybeKey);

	# TODO NOW: check for empty/null $jid

	$hitsKey = 'reg-jid_hits-'.$jid;
	$hitCount = $redis->incr($hitsKey);

	# if more than 10 hits, do NOT allow verification to occur (rate limit)
	if ($hitCount > 10) {
		# if expiry not set yet then set expiry to 10 minutes
		if ($redis->ttl($hitsKey) < 0) {
			$redis->expire($hitsKey, 600);
		}

?>
Too many verification attempts.  Please refresh this page in about 10 minutes or
<a href="../">start again</a>.
<?php
	} else {
		# TODO NOW: confirm $_GET['jcode'] is correct: 'reg-jcode-'.$jid
		#  actually strtolower($_GET['jcode'])

		# omit the IP-based blocking here; can add back if bad situation

		# TODO NOW: decide how to deal with these types of codes (below)
		# 1. credit card payment token/code
		# 2. prepaid credit code
		# 3. referral code (from user, deleted after use, may expire(?))
		#  - MUST include lots of logging in account bot so can find 'em
		# 4. referral code (from admin, unlimited use, but expires)
		# 5. signup code: sent to user with /msg <JID> so they can enter

		# TODO NOW: note on invite challenge page that you can always
		#  run your own JMP instance if you prefer none of the above

		# TODO NOW: note on invite challenge page the support avenues if
		#  people have any questions about the above (directly?)

		# TODO NOW: for 3 above, give 10 refer codes per cal month, with
		#  30-day expiry - only to people having account for 90+ days
		#  (above is implemented in JMP account bot)
		#  (note account will be frozen if bad referrals/spammers)

		# TODO NOW: print the list of options here:
		# * start paid account with credit card: more minutes! outgoing!
		# * start paid account using prepaid credit code
		# * buy prepaid credit code using Bitcoin, wire transfer, etc.
		# * start trial account using referral code
		# * start trial account without referral code (wait for confirm)

		# TODO NOW: referral code should include one I can set for a day
		#  i.e. for at conferences where lots of people may wanna signup

		# TODO NOW: test multiple refreshes of registere; should be fine
		# TODO NOW: test what happens when user tries registering DNE #
		# TODO NOW: test what happens when trying to reg already-reg'd #


		# 4 bytes is based on Gmail verification code - 9 base-10 digits
		# might have to throw this away, but easier/safer than not doing
		# TODO: check $crypto_strong param - our system ok so can defer
		$jcodeBytes = openssl_random_pseudo_bytes(4);
		$jcode = bin2hex($jcodeBytes);

		$jcodeKey = 'reg-jcode-'.$jid;
		if ($redis->setNx($jcodeKey, $jcode)) {
			$redis->expire($jcodeKey, $key_ttl_seconds);
		}

		# it's important this is last so JID expires after jcode expires
		# blow away the existing JID - if user wants to change, let them
		$jidKey = 'reg-jid_maybe-'.$_GET['sid'];
		$redis->setEx($jidKey, $key_ttl_seconds, $jid);

		$clean_sid = preg_replace('/[^0-9a-f]/', '', $_GET['sid']);

		# TODO: XEP-0106 Sec 4.3 compliance; won't work with pre-escaped
		$ej_search  = array('\\',  ' ',   '"',   '&',   "'",   '/',
			':',   '<',   '>',   '@');
		$ej_replace = array('\5c', '\20', '\22', '\26', '\27', '\2f',
			'\3a', '\3c', '\3e', '\40');
		$cheo_jid = str_replace($ej_search, $ej_replace, $jid).'@'.
			$cheogram_jid;

		$credKey = 'catapult_cred-'.$cheo_jid;
		if ($redis->exists($credKey)) {
?>
JID (<?php echo htmlentities($jid) ?>) already registered.  Please press
Back and choose a different JID or <a href="../">start again</a>.
<?php
		} else {
			# send the verification message via Cheogram
			$options = array('http' => array(
			'header'   => "Content-type: application/json\r\n",
			'method'   => 'POST',
			'content'  => '{"receiptRequested":"all",'.
				'"tag":"verify'.$jcode.$jid.' jmp-register",'.
				'"callbackUrl":"'.$fwdcalls_url.'",'.
				'"from":"'.$support_number.'",'.
				'"to":"'.$cheogram_did.'",'.
				# TODO: construct & add register4 URL to message
				'"text":"/msg '.$jid.
				' Your JMP verification code is '.
				$redis->get($jcodeKey).' - for help, '.
				'reply to this message, or text '.
				'1 (416) 993 8000 or 1 (312) 796 8000."}'
			));

			$context = stream_context_create($options);
			$result = file_get_contents("https://$tuser:$token".
				'@api.catapult.inetwork.com/v1/users/'.
				"$user/messages", false, $context);
			if ($result === FALSE) {
?>
There was an error sending your confirmation code.  Please <a href=
"../register3/?number=<?php
	echo urlencode($_GET['number']);
?>&amp;sid=<?php
	echo $clean_sid;
?>&amp;jid=<?php
	echo urlencode($jid);
?>">click here</a> or press Reload to try again or press Back to select a
different JID to use.
<?php
		        } else {
				# TODO: remove hack for register attempt notify
				$time = microtime(true);
				mail($notify_receiver_email,
					'verifying JID for '.$_GET['number'],
					'session ID: '.$clean_sid."\n".
					'Jabber ID:  '.htmlentities($jid)."\n".
					'cheo JID:   '.$cheo_jid.
					"\nemail time: $time");
?>
</p>

<h2>You've selected <?php echo $_GET['number'] ?> as your JMP number</h2>

<p>
Please enter the verification code that was just sent to your JID (<?php
	echo htmlentities($jid);
?>):
</p>

<form action="../register4/">
<p>
<input type="hidden" name="number" value="<?php echo $_GET['number'] ?>" />
<input type="hidden" name="sid" value="<?php echo $clean_sid ?>" />
Code: <input type="text" name="jcode" /> <input type="submit" value="Submit" />
</p>
</form>

<p>
If you have not yet received the verification code, then your XMPP server (or
client) may be preventing you from receiving text messages from phone numbers
that are new to you.  For details, see <a href="../#blocking">the FAQ section on
message blocking</a>.  You may switch to a different XMPP server (perhaps from
<a href="../suggested_servers.html">our suggested servers list</a>) by creating
an account at one, then pressing Back and using your new JID instead.  Or, <a
href="../register3/?number=<?php
	echo urlencode($_GET['number']);
?>&amp;sid=<?php
	echo $clean_sid;
?>&amp;jid=<?php
	echo urlencode($jid);
?>">click here</a> or press Reload to try sending the code to the same JID
again.
</p>

<p class="warning"> <!-- FIXME: add css for this class -->
<b>Note:</b> By continuing to the next step, you agree to JMP's Fair Usage
Policy, which our carriers require us to make you aware of: You will not
participate in or assist in any fraudulent usage, you acknowledge and agree that
SMS messages to or from you may be blocked by carriers or other service
providers for reasons known or unknown to JMP, your usage will be consistent
with typical human operation, each SMS message will be initiated due to human
interaction (as opposed to automated or timed messages), and you acknowledge
that JMP reserves the right to take any action necessary for JMP to comply with
any applicable CTIA and/or CRTC guidelines.
<?php
			}
		}
	}
} else {
	echo htmlentities($_GET['number']);
?>
 is not an E.164 NANP number.  Please <a href="../">start again</a>.
<?php
}
*/
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
