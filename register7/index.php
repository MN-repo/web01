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

# TODO: find "contact support" below and add appropriate details, for example:
#  * who to contact - email?  phone?  JID?
#  * include list of items to send to support (see "if (empty($_GET['tx']))")
#  * how to retry the payment process (add Subscribe button?  return to Step 6?)

if (empty($_GET['tx'])) {
?>
Payment information incomplete - no transaction ID was received.  If you believe
that your payment succeeded, please contact support to finish your registration.
To include when contacting support:
</p>
<ul><li>JMP number: "<?php echo htmlentities($_GET['jmp-number']) ?>"
</li><li>session ID: "<?php echo htmlentities($_GET['jmp-sid']) ?>"
</li><li>st/amt/cc/sig: "<?php
	echo htmlentities($_GET['st'].'/'.$_GET['amt'].'/'.$_GET['cc'].'/'.
		$_GET['sig']);
?>"
</li></ul>
<p>
<?php

} else {
	include '../../../../settings-jmp.php';

	$redis = new Redis();
	$redis->pconnect($redis_host, $redis_port);
	if (!empty($redis_auth)) {
		# TODO: check return value to confirm login successful
		$redis->auth($redis_auth);
	}

	# get phone first since it's harder to verify (in case one/both expire)
	$phone = $redis->get('reg-phn_good-'.$_GET['jmp-sid']);
	$jid = $redis->get('reg-jid_good-'.$_GET['jmp-sid']);


	# log the GET params - put in a variable keyed on transaction ID
	$pLog = '';
	$pLog .= microtime(true);
	$pLog .= "\n";
	foreach ($_GET as $get_key => $get_item) {
		if (is_array($get_item)) {
			foreach ($get_item as $array_item) {
				$pLog .= "$get_key: $get_item, $array_item\n";
			}
		} else {
			$pLog .= "$get_key: $get_item\n";
		}
	}

	$logParamsKey = 'log-paypal_log_params-'.$_GET['tx'];
	$redis->rPush($logParamsKey, $pLog);


	# setup mappings so that it's easy to get to the logs given various IDs
	$fwdLinkKey = 'log-fwd_to_sid-'.$phone;
	$redis->rPush($fwdLinkKey, $_GET['jmp-sid']);

	$jidLinkKey = 'log-jid_to_sid-'.$jid;
	$redis->rPush($jidLinkKey, $_GET['jmp-sid']);

	$jpnLinkKey = 'log-jpn_to_sid-'.$_GET['jmp-number'];
	$redis->rPush($jpnLinkKey, $_GET['jmp-sid']);

	$sidLinkKey = 'log-sid_to_tx-'.$_GET['jmp-sid'];
	$redis->rPush($sidLinkKey, $_GET['tx']);


	# first do the PDT verification to ensure user actually paid for service
	$postdata = http_build_query(array(
		'cmd'	=> '_notify-synch',
		'tx'	=> $_GET['tx'],
		'at'	=> $paypal_pdt_token
	));

	# HTTP/1.1 (and Host:) is required per http://www.tipsandtricks-hq.com
	#  /forum/topic/paypal-updates-affecting-ipn-and-pdt-scripts
	$options = array('http' => array(
		'protocol_version'	=> 1.1,
		'header'		=> "Host: www.sandbox.paypal.com\r\n".
			"Content-type: application/x-www-form-urlencoded\r\n",
		'method'		=> 'POST',
		'content'		=> $postdata
	));

	$context = stream_context_create($options);
	$result = file_get_contents('https://www.sandbox.paypal.com/cgi-bin/webscr',
		false, $context);


	# log the results string - put in a variable keyed on transaction ID
	$rLog = '';
	$rLog .= microtime(true);
	$rLog .= "\n";
	if ($result === FALSE) {
		$rLog .= "[FALSE]\n";

		foreach ($http_response_header as $k => $header_line) {
			if (is_array($header_line)) {
				foreach ($header_line as $k_item) {
					$rLog .= "$k: $header_line, $k_item\n";
				}
			} else {
				$rLog .= "$k: $header_line\n";
			}
		}
	} else {
		$rLog .= $result;
	}

	$logResultKey = 'log-paypal_log_result-'.$_GET['tx'];
	$redis->rPush($logResultKey, $rLog);


	if ($result === FALSE || substr($result, 0, 7) != 'SUCCESS') {
?>
The payment was not received or there was an error verifying your transaction.
It is unlikely that a payment was charged, but it would help to confirm this.
Please contact support with these details: ...
<?php
	} else {
		$fail_text = '';
		$result_lines = explode("\n", $result);
		foreach ($result_lines as $key_value) {
			$pair = explode('=', $key_value, 2);

			switch ($pair[0]) {
			case 'payment_status':
				if ($pair[1] != 'Completed') {
					$fail_text .= "payment not completed, ";
				}
				break;
			case 'txn_type':
				if ($pair[1] != 'subscr_payment') {
					$fail_text .= "not a subscription, ";
				}
				break;
			case 'receiver_email':
				if ($pair[1] != $paypal_receiver_email) {
					$fail_text .= "receiver incorrect, ";
				}
				break;
			case 'payment_gross':
				if ($pair[1] != $paypal_price_per_month) {
					$fail_text .= "incorrect amount paid, ";
				}
				break;
			}
		}

		if (!empty($fail_text)) {
?>
Incorrect payment information was received.  It is possible that a payment was
still sent - please contact support to correct and/or refund, including this:
...
</p>
<ul><li>JMP number: "<?php echo htmlentities($_GET['jmp-number']) ?>"
</li><li>session ID: "<?php echo htmlentities($_GET['jmp-sid']) ?>"
</li><li>failure reason: <?php echo $fail_text ?>
</li></ul>
<p>
<?php
		} elseif (empty($_GET['jmp-number']) ||
			empty($_GET['jmp-sid'])) {
?>
Payment was received, but session ID and/or number were not provided.  Please
contact support to select a number and complete your registration, including the
following transaction ID: <?php echo htmlentities($_GET['tx']) ?>.
<?php
		# TODO: update "== 12" for when we support non-NANPA numbers
		} elseif (strlen($_GET['jmp-number']) == 12 &&
			$_GET['jmp-number'][0] == '+'
			&& is_numeric(substr($_GET['jmp-number'], 1))) {

			# if user made it this far, $jid and $phone better exist
			if (!$jid || !$phone) {
?>
Payment was received, but the verification for the phone number and/or Jabber ID
has expired.  Please contact support to complete the registration, including the
following transaction ID: <?php echo htmlentities($_GET['tx']) ?>.
<?php
			} elseif ($redis->get('catapult_num-'.$_GET['tx']) &&
				$redis->get('catapult_num-'.$_GET['tx']) !=
				$_GET['jmp-number']) {
?>
Your payment details have already been used to activate a different JMP number.
If you believe this is an error, please contact support with this information:
<?php
			} else {
				# TODO: buy $_GET['jmp-number']; message if fail
				# TODO: check $_GET['jmp-number'] works b4 using
				# TODO: set Catapult app for $_GET['jmp-number']

				# for now assume bought and correct app assigned

				# this transaction has been used for activation
				$redis->set('catapult_num-'.$_GET['tx'],
					$_GET['jmp-number']);

				# re-create catapult_cred-$jid w real JMP number
				$credKey = 'catapult_cred-'.$jid;
				$redis->del($credKey);

				$redis->rPush($credKey, $user);
				$redis->rPush($credKey, $tuser);
				$redis->rPush($credKey, $token);
				$redis->rPush($credKey, $_GET['jmp-number']);

				# we'll let old catapult_fwd-$reg_tmp_num expire
				$redis->set('catapult_fwd-'.$_GET['jmp-number'],
					$phone);

				# we'll let old catapult_jid-$reg_tmp_num expire
				$redis->set('catapult_jid-'.$_GET['jmp-number'],
					$jid);

				# TODO: fix below txt to better indicate success
?>
</p>

<h2><?php echo $_GET['jmp-number'] ?> is now your JMP number!</h2>

<p>TODO TODO TODO - actually buy number so below is necessarily true - TODO</p>

<p>
Success!  Text messages to/from <?php echo $_GET['jmp-number'] ?> can be
received/sent from your Jabber ID (<?php echo $jid ?>) while calls to
<?php echo $_GET['jmp-number'] ?> will be forwarded to <?php echo $phone ?>.
<?php
			}
		} else {
			echo htmlentities($_GET['jmp-number']);
?>
, the JMP number that you attempted to activate, is not an E.164 NANP number.
Payment was received, but the number is not available for use with JMP.  Please
contact support to select a number and complete your registration, including the
following transaction ID: <?php echo htmlentities($_GET['tx']) ?>.
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
