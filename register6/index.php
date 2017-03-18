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

include '../../../../settings-jmp.php';

$redis = new Redis();
$redis->pconnect($redis_host, $redis_port);
if (!empty($redis_auth)) {
	# TODO: check return value to confirm login succeeded
	$redis->auth($redis_auth);
}
$jmpnum = $redis->get('reg-num_vjmp-'.$_GET['sid']);

if (empty($_GET['pcode'])) {
?>
Verification code not entered.  Please press Back and enter a verification code
or <a href="../#support">contact support</a>.
<?php
} elseif (empty($_GET['sid'])) {
?>
Session ID empty.  Please <a href="../#support">contact support</a>.
<?php

} elseif (!$jmpnum) {
?>
It looks like you haven't registered a JMP number yet or registered one a while
ago.  To setup call forwarding options for a JMP number, please
<a href="../">signup for one</a> or 
<a href="../#support">contact support</a>.
<?php

} else {
	$phoneMaybeKey = 'reg-phn_maybe-'.$_GET['sid'];

	if ($_GET['pcode'] == 'nofwdnum') {
		# TODO: make this a little prettier and/or check return values
		$redis->set($phoneMaybeKey, '');
	}

	$phone = $redis->get($phoneMaybeKey);
	# TODO: check if $phone is non-empty, etc.

	$hitsKey = 'reg-phn_hits-'.$phone;
	$hitCount = 0;
	if ($_GET['pcode'] != 'nofwdnum') {
		$hitCount = $redis->incr($hitsKey);
	}

	$pcodeKey = 'reg-pcode-'.$phone;

	$clean_sid = preg_replace('/[^0-9a-f]/', '', $_GET['sid']);

	# if more than 10 hits, do NOT allow verification to occur (rate limit)
	if ($hitCount > 10) {
		# if expiry not set yet then set expiry to 10 minutes
		if ($redis->ttl($hitsKey) < 0) {
			$redis->expire($hitsKey, 600);
		}

?>
Too many verification attempts.  Please refresh this page in about 10 minutes or
<a href="../#support">contact support</a>.
<?php
	} elseif ($_GET['pcode'] != 'nofwdnum' &&
		strtolower($_GET['pcode']) != $redis->get($pcodeKey)) {
?>
</p>
<form action="../register6/">
<p>
<input type="hidden" name="sid" value="<?php echo $clean_sid ?>" />
Invalid verification code (<?php echo htmlentities($_GET['pcode']) ?>).  Please
enter a new code to try again: <input type="text" name="pcode" />
<input type="submit" value="Submit" />
</p>
</form>
<p>
<?php
	} else {
		if (is_null($phone)) {
?>
Could not find phone number (<?php echo $phone ?>)
associated with this session ID (<?php echo $clean_sid ?>).  Please
<a href="../#support">contact support</a>.
<?php
		} else {
			# we overwrite old value - use last of verified phone #s
			$redis->set('catapult_fwd-'.$jmpnum, $phone);
			# TODO: confirm that SET worked correctly
?>
</p>

<h2>Your JMP number is <?php echo $jmpnum ?></h2>

<?php
			if (!empty($phone)) {
?>
<p>
Your forwarding number (<?php echo $phone ?>) has been successfully verified!
</p>
<?php
			}
?>

<p>
<?php
			if (empty($phone)) {
?>
Callers will hear "This phone number does not receive voice calls; please send a
text message instead".
<?php
			} else {
?>
All phone calls to your JMP number will to be forwarded to <?php echo $phone ?>.
<?php
			}
?>
</p>

<p>
If you'd like to change or setup your forwarding number, please press Back twice
and enter the details you'd like to use.  Or
<a href="../#support">contact support</a> at any time.
</p>

<p>
You're all set!  You can receive up to 30 total minutes of voice calls during
the trial period and you may send up to 300 text or picture messages, or
<a href="../upgrade1/">upgrade to a paid account</a> at any time during this
period to receive unlimited text and picture messages for the duration of the
JMP beta, and to keep your JMP number (it will be reclaimed after the trial if
you don't upgrade to a paid account and don't
<a href="https://www.fcc.gov/consumers/guides/porting-keeping-your-phone-number-when-you-change-providers">port</a>
your JMP number to another provider).
<?php
		}
	}
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
