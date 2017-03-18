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
<form action="../register1/">
<p>
Area code: <input type="text" name="areacode" /> (ie. 613 for Ottawa area or 617
for Boston area) <input type="submit" value="Submit" />
</p>
</form>
<p>
<?php

if (empty($_GET['areacode'])) {
?>
Please enter an area code above and press Submit to get a list of numbers.
<?php
} elseif (strlen($_GET['areacode']) == 3 && is_numeric($_GET['areacode'])) {
	include '../../../../settings-jmp.php';

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=5000&areaCode=".$_GET['areacode'];

	$num_list = file_get_contents($url);
	$num_list = json_decode($num_list, true);

	if (empty($num_list)) {
		echo htmlentities($_GET['areacode']);
?>
 does not have any numbers available.  Please try another area code (<a href=
"https://en.wikipedia.org/wiki/List_of_North_American_Numbering_Plan_area_codes"
>complete list</a>).

<?php
	} else {
?>
Please choose one of the following numbers, or if none seem interesting enough,
try another area code (<a href=
"https://en.wikipedia.org/wiki/List_of_North_American_Numbering_Plan_area_codes"
>complete list</a>):
</p>
<table>
<tr><th>number</th><th>rate centre</th><th>city</th><th>province/state</th></tr>
<?php foreach ($num_list as $number): ?>
<tr>
	<td><a href="../register2/?number=<?php
		echo urlencode($number["number"]).'&city='.urlencode(
			str_replace(' - ', '-', ucwords(strtolower(str_replace(
				'-', ' - ', $number["city"])))).
			', '.$number["state"]);
	?>"><?php echo $number["nationalNumber"] ?></a></td>
	<td><?php echo $number["rateCenter"] ?></td>
	<td><?php echo $number["city"] ?></td>
	<td><?php echo $number["state"] ?></td>
</tr>
<?php endforeach; ?>
</table>
<p>
<?php
	}
} else {
	echo htmlentities($_GET['areacode']);
?>
 is not a valid area code.  Please enter a different one above (<a href=
"https://en.wikipedia.org/wiki/List_of_North_American_Numbering_Plan_area_codes"
>complete list</a>).
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
