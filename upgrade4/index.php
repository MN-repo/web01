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
} elseif (intval($_GET['amount_sat']) < 150000) {
	error_log('mError - amount ('.$_GET['amount_sat'].') too low');
?>
<p>
The amount entered is too low.  Please <a href="../upgrade1/">start again</a>.
<?php
} else {
	include '../../../../settings-jmp.php';

	# create the payment request
	$request_details = array(
		'jsonrpc'	=> '2.0',
		'id'		=> $electrum_id_prefix.'-'.microtime(TRUE),
		'method'	=> 'add_request',
		'params'	=> array(
			'expiration'	=> 10800,
			'amount'	=> strval(intval($_GET['amount_sat'])
						/ 100000000),
			'memo'		=> 'payment_for_'.$_GET['bc_id']
		)
	);

	$options = array('http' => array(
		'header'   => "Content-type: application/json\r\n",
		'method'   => 'POST',
		'content'  => json_encode($request_details)
	));

	$context = stream_context_create($options);
	$result = file_get_contents('http://'.$electrum_rpc_username.':'.
				$electrum_rpc_password.'@127.0.0.1:'.
				$electrum_rpc_port, false, $context);

	if ($result === FALSE) {
		error_log('pError - could not create payment request');
?>
<p>
There was an error creating your payment request.  Please press Reload to try
again or <a href="../upgrade1/">start from the beginning</a>.
<?php
        } else {
		$details = json_decode($result, true);
		# TODO: remove hack for payment attempt notify
		$time = microtime(TRUE);
		mail($notify_receiver_email,
			'paying for '.htmlentities($_GET['bc_id']),
			'amount: '.$_GET['amount_sat']."\n".
			'email time: '.$time."\n".
			'JSON: '.$result
		);

		$address = $details['result']['address'];


		header('Location: '.$electrum_url_prefix.$address, TRUE, 303);
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
