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
<?php @include_once __DIR__ . '/../vendor/go.php'; ?>
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

	$jid = $redis->get('catapult_jid-+'.$_GET['bc_id']);
	if ($jid === FALSE) {
		// TODO: we shouldn't have to know about cheogram.com
		// TODO: XEP-0106 Sec 4.3 compliance; pre-escaped'll fail
		$ej_search  = array('\\',  ' ',   '"',   '&',   "'",
			'/',   ':',   '<',   '>',   '@');
		$ej_replace = array('\5c', '\20', '\22', '\26', '\27',
			'\2f', '\3a', '\3c', '\3e', '\40');
		$jid = str_replace($ej_search, $ej_replace, $_GET['bc_id']).
			'@'.$cheogram_jid;
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
	$address = $redis->evalSha(
		'11bde988dd77485c7986941488c202d1f9c67cd9',
		[
			'jmp_available_btc_addresses',
			'jmp_customer_btc_addresses-'.$customer_id
		],
		2
	);

	if (!$address) {
		error_log('pError - could not create address');
?>
<p>
There was an error creating your payment request.  Please press Reload to try
again or <a href="../upgrade1/">start from the beginning</a>.
<?php
        } else {

		if ($_GET['currency'] == 'CAD') {
			$redis->set(
				'pending_plan_for-'.$customer_id,
				'cad_beta_unlimited-v20210223'
			);
		}

		if ($_GET['currency'] == 'USD') {
			$redis->set(
				'pending_plan_for-'.$customer_id,
				'usd_beta_unlimited-v20210223'
			);
		}

		// TODO: no need to use a public URL here
		$notify = 'https://pay.jmp.chat/electrum_notify';
		$notify .= '?address=' . urlencode($address);
		$notify .= '&customer_id=' . urlencode($customer_id);
		electrum_rpc('notify', array(
			'address' => $address,
			'URL'     => $notify
		));

		if (empty($_GET['number']) or empty($_GET['sid'])) {
?>
<p>Send any amount of BTC to this address:</p>
<kbd>
<?php
			echo $address;
?>
</kbd><p>You will be notified when your transaction has 3 confirmations.
<?php
		} else {
			$redis->setEx(
				'reg-sid_for-'.$customer_id,
				$key_ttl_seconds,
				$_GET['sid']
			);

			// Store the tel we're working with, for possible later use
			$sessionTel = 'reg-session_tel-'.$_GET['sid'];
			$redis->setEx($sessionTel, $key_ttl_seconds, $_GET['number']);
?>
<p>Send a minimum of 0.0005 BTC to this address:</p>
<tt>
<?php
			echo $address;
?>
</tt><p>You will be notified when your transaction has 3 confirmations.
<?php
		}
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
