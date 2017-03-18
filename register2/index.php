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

if (empty($_GET['number'])) {
?>
No phone number entered.  Please <a href="../">choose one</a>.
<?php

# TODO: update "== 12" for when we support non-NANPA numbers
} elseif (strlen($_GET['number']) == 12 && $_GET['number'][0] == '+' &&
	is_numeric(substr($_GET['number'], 1))) {

	$sid = $_GET['sid'];
	if (empty($sid)) {
		# TODO: check $crypto_strong param - our system ok so can defer
		$sidBytes = openssl_random_pseudo_bytes(16);
		$sid = bin2hex($sidBytes);
	}

	$clean_sid = preg_replace('/[^0-9a-f]/', '', $sid);
?>
</p>

<h2>You've selected <?php
	echo $_GET['number'].' ('.htmlentities($_GET['city']).')';
?> as your JMP number</h2>

<p>
Please enter the Jabber ID (JID) for your XMPP account and press Submit (a
Jabber ID normally looks like an email address, of the form user@example.com).
A verification code will be sent to it that you will enter on the next page.
</p>

<p>
If you don't have a Jabber ID, you can get one with the <a href=
"https://conversations.im/">Conversations app</a> (Android).  Or, you can get
one from <a href="https://xmpp.net/directory.php">one of these servers</a> using
<a href="https://xmpp.org/software/clients.html">one of these apps</a> for
Debian, iOS, and most other OSes.
</p>

<form action="../register3/">
<p>
<input type="hidden" name="number" value ="<?php echo $_GET['number'] ?>" />
<input type="hidden" name="sid" value ="<?php echo $clean_sid ?>" />
JID: <input type="text" name="jid" /> <input type="submit" value="Submit" />
</p>
</form>
<p>
<?php
} else {
	echo htmlentities($_GET['number']);
?>
 is not an E.164 NANP number.  Please <a href="../">choose a new number</a>.
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
