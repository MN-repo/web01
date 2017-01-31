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

if (empty($_GET['pcode'])) {
?>
Verification code not entered.  Please press Back and enter a verification code
or <a href="../">start again</a>.
<?php
} elseif (empty($_GET['number']) || empty($_GET['sid'])) {
?>
Session ID and/or number empty.  Please <a href="../">start again</a>.
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

	$pcodeKey = 'reg-pcode-'.$_GET['sid'];

	$phoneMaybeKey = 'reg-phn_maybe-'.$_GET['sid'];
	$phoneGoodKey = 'reg-phn_good-'.$_GET['sid'];

	$maybePhone = $redis->get($phoneMaybeKey);
	# TODO: check if $maybePhone is non-empty, etc.

	$hitsKey = 'reg-phn_hits-'.$maybePhone;
	$hitCount = $redis->incr($hitsKey);

	$clean_sid = preg_replace('/[^0-9a-f]/', '', $_GET['sid']);

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
	} elseif ($_GET['pcode'] != $redis->get($pcodeKey)) {
?>
</p>
<form action="../register6/">
<p>
<input type="hidden" name="number" value="<?php echo $_GET['number'] ?>" />
<input type="hidden" name="sid" value="<?php echo $clean_sid ?>" />
Invalid verification code (<?php echo htmlentities($_GET['pcode']) ?>).  Please
enter a new code to try again: <input type="text" name="pcode" />
<input type="submit" value="Submit" />
</p>
</form>
<p>
<?php
	} else {
		# we overwrite old value - if multiple phones verified, use last
		if (!$redis->rename($phoneMaybeKey, $phoneGoodKey)) {
			# TODO: provide some sort of error due to failed rename;
			#  this most likely'd happen if they refreshed this page
			#  (ie. due to _maybe already being moved to _good)
		}

		$phone = $redis->get($phoneGoodKey);

		$jidGoodKey = 'reg-jid_good-'.$_GET['sid'];
		$jid = $redis->get($jidGoodKey);

		if (!$phone || !$jid || empty($phone) || empty($jid)) {
?>
Could not find phone number (<?php echo $phone ?>) and/or JID (<?php
	echo htmlentities($jid);
?>) associated with this session ID (<?php echo $clean_sid ?>).  Please
<a href="../">start again</a>.
<?php
		} else {
?>
</p>

<h2>You've selected <?php echo $_GET['number'] ?> as your JMP number</h2>

<p>
To confirm, you plan to use the Jabber ID <?php echo htmlentities($jid) ?> to
send and receive text and pictures messages on your JMP number, and you intend
for all phone calls to your JMP number to be forwarded to <?php echo $phone ?>.
</p>

<p>
Is that correct?  If so, please press the [TODO:name] button below to finish the
registration and start using your JMP number!
</p>

<p>
[TODO:name] button
<?php
		}
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
