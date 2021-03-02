<?php

$rcv_time = microtime(TRUE);

include '../../../settings-jmp.php';

$redis = new Redis();
$redis->pconnect($redis_host, $redis_port);
if (!empty($redis_auth)) {
	# TODO: check return value to confirm login succeeded
	$redis->auth($redis_auth);
}

$jid = $redis->get('catapult_jid-+'.$_GET['bc_id']);
if ($jid === FALSE) {
	if ($redis->exists('catapult_cred-'.$_GET['bc_id']) > 0) {
		$jid = $_GET['bc_id'];
	} else {
		# TODO: XEP-0106 Sec 4.3 compliance; pre-escaped'll fail
		$ej_search  = array('\\',  ' ',   '"',   '&',   "'",
			'/',   ':',   '<',   '>',   '@');
		$ej_replace = array('\5c', '\20', '\22', '\26', '\27',
			'\2f', '\3a', '\3c', '\3e', '\40');
		$jid = str_replace($ej_search, $ej_replace, $_GET['bc_id']).
			'@'.$cheogram_jid;
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

$now = time();
$ppaoKeyThisMo = 'payment-plan_as_of_'.date('Ym', $now).'-'.$jid;
$ppaoKeyNextMo = 'payment-plan_as_of_'.date('Ym', strtotime('+1 month', $now)).
	'-'.$jid;

$rv1 = $redis->setNx($ppaoKeyThisMo, 'xxx_stable_trial-v20200913');
$rv2 = $redis->setNx($ppaoKeyNextMo, 'xxx_stable_trial-v20200913');

$time = microtime(TRUE);
mail($notify_receiver_email,
	'electrum PAID for '.htmlentities($_GET['bc_id']),
	'rcved time: '.$rcv_time."\n".
	'email time: '.$time."\n".
	'msg:  '.$request['result']['message']."\n".
	'addr: '.htmlentities($_GET['address'])."\n".
	'bc_id: '.$_GET['bc_id']."\n".
	'cheo: '.$jid."\n".
	'rv1:  '.$rv1."\n".
	'rv2:  '.$rv2."\n".
	'JSON: '.$request['result']['status_str']
);

echo 'DONE';
?>
