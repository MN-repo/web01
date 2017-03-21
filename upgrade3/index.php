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
</style>
</head>
<body style="padding: 0 5%;">
<div style="text-align:center;">
<a href="../"><img src="../static/jmp_beta.png" alt="JMP - beta" /></a>
</div>
<p>

<?php

if (empty($_GET['tx'])) {
?>
Payment information incomplete - no transaction ID was received.  If you believe
that your payment succeeded, please contact support to finish your registration.
Otherwise, please <a href="../upgrade1/">start again</a> to complete the
payment.  You can contact support by replying to the verification message you
received in your XMPP client (or, equivalently, send an XMPP message to <a href=
"xmpp:+14169938000@cheogram.com">+14169938000@cheogram.com</a>), sending a text
message with the details to +1 416 993 8000 (Canada) or +1 312 796 8000 (US), or
by sending a private message to "ossguy" in the JMP/Soprani.ca MUC at <a
href="xmpp:discuss@conference.soprani.ca?join">discuss@conference.soprani.ca</a>
.  Please include the above as well as the following when contacting support:
</p>
<ul><li>JMP number: "<?php echo htmlentities($_GET['jmp-number']) ?>"
</li><li>Jabber ID: "<?php echo htmlentities($_GET['jmp-jid']) ?>"
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
	# NOTE: JID or number will be empty, so the "..to_tx-" list'll get large
	$jidLinkKey = 'log-jid_to_tx-'.$_GET['jmp-jid'];
	$redis->rPush($jidLinkKey, $_GET['tx']);

	$jpnLinkKey = 'log-jpn_to_tx-'.$_GET['jmp-number'];
	$redis->rPush($jpnLinkKey, $_GET['tx']);


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
	$result = file_get_contents(
		'https://www.sandbox.paypal.com/cgi-bin/webscr',
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
If you know that no payment was charged, you can <a href="../upgrade1/">start
again</a>.  Or, contact support by replying to the verification message you
received in your XMPP client (or, equivalently, send an XMPP message to <a href=
"xmpp:+14169938000@cheogram.com">+14169938000@cheogram.com</a>), sending a text
message with the details to +1 416 993 8000 (Canada) or +1 312 796 8000 (US), or
by sending a private message to "ossguy" in the JMP/Soprani.ca MUC at <a
href="xmpp:discuss@conference.soprani.ca?join">discuss@conference.soprani.ca</a>
.  Please include the above as well as the following when contacting support:
</p>
<ul><li>JMP number: "<?php echo htmlentities($_GET['jmp-number']) ?>"
</li><li>Jabber ID: "<?php echo htmlentities($_GET['jmp-jid']) ?>"
</li><li>transaction ID: "<?php echo htmlentities($_GET['tx']) ?>"
</li></ul>
<p>
<?php
	} else {
		$id_text = '';
		if (!empty($_GET['jmp-number'])) {
			$id_text = 'JMP number '.
				htmlentities($_GET['jmp-number']);
		} elseif (!empty($_GET['jmp-jid'])) {
			$id_text = 'Jabber ID (JID) '.
				htmlentities($_GET['jmp-jid']);
		} else {
			$id_text = 'unknown identifier';
		}
?>
</p>
<div style="text-align:center;">
<h3>You've been upgraded!</h3>
</div>

<p>
We've received your payment for the JMP account with <?php echo $id_text ?>.
Your paid account upgrade is complete - you can now send unlimited text and
picture messages!
</p>

<p>
Note that unlimited text and picture messages will last for the duration of your
current payment period.  After that, if JMP is still in beta (as it will be
until at least June 2017), your account will auto-renew with unlimited text and
picture messages.  If JMP is no longer in beta, then your account will be
automatically updated to a new account type with limited outgoing text and
picture messages, but will keep unlimited incoming text and picture messages.
There will be other account options at that time if you wish to keep the
unlimited outgoing text and picture messages.
</p>

<p>
In the unlikely event that there is ever an issue with a JMP payment, we will
reach out to you by messaging your JMP number within 5 business days to resolve
it.  If you ever have any questions or concerns, please feel free to
<a href="../#support">contact us</a>.  Thanks for being a JMP customer!
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
