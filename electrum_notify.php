<?php

require_once dirname(__FILE__).'../../../../settings-jmp.php';

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

$request = electrum_rpc('getrequest', array('key' => $_GET['address']));

if ($request === FALSE || !is_array($request['result'])) {
	die('Could not find request for: '.$_GET['address']);
}

if ($request['result']['status_str'] != 'Paid') {
	die('The request for '.$_GET['address'].' is not paid.');
}

if (!strstr($request['result']['message'], $_GET['bc_id'])) {
	die('The request for '.$_GET['address'].' is not for '.$jid);
}

$month = date('Ym');
$redis->setNx("payment-plan_as_of_$month-$jid", 'xxx_stable_trial-v20200913');

echo 'DONE';
