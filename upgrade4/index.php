<?php ob_start(); ?>
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
</head>
<body>
<?php

if (empty($_GET['bc_id'])) {
	error_log('mError - no payment ID');
?>
<p>
ID not entered.  Please <a href="../upgrade1/">start again</a>.
<?php
} elseif (empty($_GET['amount_sat'])) {
	error_log('mError - no payment amount');
?>
<p>
Amount not entered.  Please <a href="../upgrade1/">start again</a>.
<?php
} elseif (intval($_GET['amount_sat']) < 20000) {
	error_log('mError - amount ('.$_GET['amount_sat'].') too low');
?>
<p>
The amount entered is too low.  Please <a href="../upgrade1/">start again</a>.
<?php
} else {
	include '../../../../settings-jmp.php';
	require_once dirname(__FILE__).'/../lib/braintree_php/lib/Braintree.php';
	$braintree = new Braintree\Gateway($braintree_config); // settings-jmp.php

	$redis = new Redis();
	$redis->pconnect($redis_host, $redis_port);
	if (!empty($redis_auth)) {
		# TODO: check return value to confirm login succeeded
		$redis->auth($redis_auth);
	}

	$jid = $redis->get('catapult_jid-+1'.$_GET['bc_id']);
	if ($jid === FALSE) {
		if ($redis->exists('catapult_cred-'.$_GET['bc_id']) > 0) {
			$jid = $_GET['bc_id'];
		} else {
			// TODO: we shouldn't have to know about cheogram.com
			// TODO: XEP-0106 Sec 4.3 compliance
			$jid = str_replace("\\", "\\\\5c",
				str_replace(' ', "\\\\20",
				str_replace('"', "\\\\22",
				str_replace('&', "\\\\26",
				str_replace("'", "\\\\27",
				str_replace('/', "\\\\2f",
				str_replace(':', "\\\\3a",
				str_replace('<', "\\\\3c",
				str_replace('>', "\\\\3e",
				str_replace('@', "\\\\40",
				$_GET['bc_id']
			)))))))))).'@cheogram.com';

			if ($redis->exists('catapult_cred-'.$jid) < 1) {
				die('No account found for: '.$_GET['bc_id']);
			}
		}
	}

	$customer_id = $redis->get('jmp_customer_id-' . $jid);
	if (!$customer_id) {
		$result = $braintree->customer()->create();
		if (!$result->success) {
			die('Could not create customer');
		}

		$customer_id = $result->customer->id;

		$redis->setNx('jmp_customer_id-' . $jid, $customer_id);
		$redis->setNx('jmp_customer_jid-' . $customer_id, $jid);
	}

	function electrum_rpc($method, $params) {
		global $electrum_id_prefix, $electrum_rpc_username,
			$electrum_rpc_password, $electrum_rpc_port;

		$rpc_id = $electrum_id_prefix.'-'.microtime(TRUE);
		$context = stream_context_create(array('http' => array(
			'header' => "Content-type: application/json\r\n",
			'method' => 'POST',
			'content' => json_encode(array(
				'jsonrpc' => '2.0',
				'id'      => $rpc_id,
				'method'  => $method,
				'params'  => $params
			))
		)));

		$auth = $electrum_rpc_username.':'.$electrum_rpc_password;
		$url = 'http://'.$auth.'@127.0.0.1:'.$electrum_rpc_port;
		$result = file_get_contents($url, false, $context);

		if ($result === FALSE) return $result;
		return json_decode($result, true);
	}

	$amount = intval($_GET['amount_sat']) / 100000000;
	$details = electrum_rpc('add_request', array(
		'expiration' => 10800,
		'amount'     => strval($amount),
		'memo'       => 'payment_for_'.$customer_id
	));

	if ($details === FALSE) {
		error_log('pError - could not create payment request');
?>
<p>
There was an error creating your payment request.  Please press Reload to try
again or <a href="../upgrade1/">start from the beginning</a>.
<?php
        } else {
		# TODO: remove hack for payment attempt notify
		$time = microtime(TRUE);
		mail($notify_receiver_email,
			'paying for '.htmlentities($_GET['bc_id']),
			'amount: '.$_GET['amount_sat']."\n".
			'email time: '.$time."\n".
			'JSON: '.json_encode($details)
		);

		$address = $details['result']['address'];

		// TODO: no need to use a public URL here
		$notify = 'https://jmp.chat/sp1a/electrum_notify.php';
		$notify .= '?address=' . urlencode($address);
		$notify .= '&customer_id=' . urlencode($customer_id);

		// Auth with hmac so we can trust the address+customer_id pair
		// Not needed for requests, but for deposit addresses we will
		$notify .= '&hmac=' . urlencode(hash_hmac(
			"sha256",
			$address.$customer_id,
			$hmac_key // jmp-settings.php
		));
		electrum_rpc('notify', array(
			'address' => $address,
			'URL'     => $notify
		));

		if (empty($_GET['number']) or empty($_GET['sid'])) {
			header('Location: '.$electrum_url_prefix.$address, TRUE, 303);
		} else {
			header('Location: '.$electrum_url_prefix.$address.
				'&number='.urlencode($_GET['number']).'&sid='.
				urlencode($_GET['sid']), TRUE, 303);
		}
		ob_end_clean();
		exit;
	}
}
?>
</p>
<hr />
<p>
Copyright &copy; 2017, 2020 <a href="https://ossguy.com/">Denver Gingerich</a>
and others.  jmp-register is licensed under AGPLv3+.
You can download the Complete Corresponding Source code <a
href="https://gitlab.com/ossguy/jmp-register">here</a>.
</p>
</body>
</html>
