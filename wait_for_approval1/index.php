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

if (empty($_GET['number'])) {
	error_log('sError - no JMP number');
?>
</head>
<body>
<p>
Number not entered.  Please <a href="../">start again</a>.
<?php
} elseif (empty($_GET['sid'])) {
	error_log('sError - no session ID (sid) provided');
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

	$jidDefinitelyKey = 'reg-jid_definitely-'.$_GET['sid'];
	$jid = $redis->get($jidDefinitelyKey);

	if (!$jid) {
		# TODO: confirm below texts are correct
		error_log('jError when verifying sid '.$_GET['sid']);
?>
</head>
<body>
<p>
Could not find JID to verify; perhaps it has already been verified.  Feel free
to <a href="../">start again</a> if not.
</p>
<hr />
<p>
Copyright &copy; 2017, 2020 <a href="https://ossguy.com/">Denver Gingerich</a>
and others.  jmp-register is licensed under AGPLv3+.  You can download the
Complete Corresponding Source code <a
href="https://gitlab.com/ossguy/jmp-register">here</a>.
</p>
</body>
</html>
<?php
		exit;
	}

	$clean_sid = preg_replace('/[^0-9a-f]/', '', $_GET['sid']);

	if (strlen($_GET['number']) == 12 && $_GET['number'][0] == '+' &&
		is_numeric(substr($_GET['number'], 1))) {

		# TODO: at least check return values of below two calls
		$sessNum = $redis->incr('reg-pending_approval_session-total');
		$redis->setEx('reg-pending_approval_session-'.$sessNum,
			$key_ttl_seconds * 2, $_GET['sid']);

		# make an ASCII character representing this session: 'a' to 'z'
		$sessionChar = chr(97 + ($sessNum % 26));


		$details = 'no info';

		$infoKey = 'mmgp_result-'.$_SERVER['REMOTE_ADDR'];
		$infoJSON = $redis->get($infoKey);

		if ($infoJSON) {
			$info = json_decode($infoJSON, true);

			$details = $info['traits']['isp'];

			if (array_key_exists('is_anonymous_proxy',
				$info['traits']) &&
				$info['traits']['is_anonymous_proxy']) {

				$details .= ', anonymous proxy';
			} else {
				$details .= ' in '.$info['city']['names']['en'];
			}

			if (array_key_exists('subdivisions', $info) &&
				array_key_exists(0, $info['subdivisions'])) {

				$details .= ', '.$info[
					'subdivisions'][0]['names']['en'];
			}

			$details .= ', ';
			$details .= $info['registered_country']['names']['en'];
			$details .= ' - ';

			$details .= $info['maxmind']['queries_remaining'];
			$details .= ' left';
		}

		# send the registration request message: informational, no block
		$options = array('http' => array(
			'header'   => "Content-type: application/json\r\n",
			'method'   => 'POST',
			'content'  => '{"receiptRequested":"all",'.
				'"tag":"signup'.$jid.' jmp-register",'.
				'"callbackUrl":"'.$fwdcalls_url.'",'.
				'"from":"'.$support_number.'",'.
				'"to":"'.$cheogram_did.'",'.
				# TODO NOW: add link/reply code for approving
				'"text":"/msg '.$notify_pending_signup_jid.
				' '.$sessionChar.'.'.
				' At '.gmdate("Y-m-d H:i:s").'Z wanting JMP # '.
				htmlentities($_GET['number']).' from '.
				$_SERVER['REMOTE_ADDR'].' ('.$details.'); JID '.
				$jid.' - informational for now, but '.
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
"../wait_for_approval1/?number=<?php
	echo urlencode($_GET['number']);
?>&amp;sid=<?php
	echo $clean_sid;
?>">click here</a> or press Reload to try again.
<?php
	        } else {
?>
<meta http-equiv="refresh" content="3;url=../register4/?number=<?php
	echo urlencode($_GET['number']);
?>&sid=<?php
	echo $clean_sid;
?>" />
</head>
<body>

<h2>Processing registration (part 2 of 2)...</h2>

<p>
If this page has been displayed for more than 30 seconds please <a href=
"../register4/?number=<?php
	echo urlencode($_GET['number']);
?>&amp;sid=<?php
	echo $clean_sid;
?>">click here</a> to proceed.

<?php
		}
	} else {
		error_log('nError when trying to reg number "'.$_GET['number'].
			'" with JID "'.$jid.'" from session ID '.$clean_sid);
?>
</head>
<body>
<p>
<?php
				echo htmlentities($_GET['number']);
?>
 is not an E.164 NANP number.  Please <a href="../">start again</a>.
<?php
	}
}
?>
</p>
<hr />
<p>
Copyright &copy; 2017, 2020 <a href="https://ossguy.com/">Denver Gingerich</a>
and others.  jmp-register is licensed under AGPLv3+.  You can download the
Complete Corresponding Source code <a
href="https://gitlab.com/ossguy/jmp-register">here</a>.
</p>
</body>
</html>
