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

if (empty($_GET['jid'])) {
?>
Jabber ID (JID) not entered.  Please press Back and enter a JID or
<a href="../">start again</a>.
<?php
} elseif (empty($_GET['number'])) {
?>
Number not entered.  Please <a href="../">start again</a>.
<?php
} elseif (empty($_GET['sid'])) {
?>
No session ID found.  Please <a href="../">start again</a>.
<?php
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
	$hitsKey = 'reg-jid_hits-'.$_GET['jid'];
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
		# 4 bytes is based on Gmail verification code - 9 base-10 digits
		# might have to throw this away, but easier/safer than not doing
		# TODO: check $crypto_strong param - our system ok so can defer
		$jcodeBytes = openssl_random_pseudo_bytes(4);
		$jcode = bin2hex($jcodeBytes);

		$jcodeKey = 'reg-jcode-'.$_GET['sid'];
		if ($redis->setNx($jcodeKey, $jcode)) {
			$redis->expire($jcodeKey, $key_ttl_seconds);
		}

		# it's important this is last so JID expires after jcode expires
		# blow away the existing JID - if user wants to change, let them
		$jidKey = 'reg-jid_maybe-'.$_GET['sid'];
		$redis->setEx($jidKey, $key_ttl_seconds, $_GET['jid']);

		$clean_sid = preg_replace('/[^0-9a-f]/', '', $_GET['sid']);

		# TODO: send verification code 2 $_GET['jid'] w Bandwidth AP API
		$jcode = $redis->get($jcodeKey);
?>
		TODO TODO TODO remove - verification code is <?php
	echo $jcode;
?> - remove TODO TODO TODO
</p>

<h2>You've selected <?php echo $_GET['number'] ?> as your JMP number</h2>

<p>
Please enter the verification code that was just sent to your JID (<?php
	echo htmlentities($_GET['jid']);
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
If you have not yet received the verification code, please <a href=
"../register3/?number=<?php
	echo urlencode($_GET['number']);
?>&amp;sid=<?php
	echo urlencode($_GET['sid']);
?>&amp;jid=<?php
	echo urlencode($_GET['jid']);
?>">click here</a> to try again.
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
