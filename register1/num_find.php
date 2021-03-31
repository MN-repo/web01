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
<title>JMP</title>
</head>
<body>
<?php

$start_time = microtime(true);

include '../../../../settings-jmp.php';
include '../../../../fancynum-jmp.php';

$redis = new Redis();
$redis->pconnect($redis_host, $redis_port);
if (!empty($redis_auth)) {
	# TODO: check return value to confirm login succeeded
	$redis->auth($redis_auth);
}

$result = '';
$resultKey = 'mmgp_result-'.$_SERVER['REMOTE_ADDR'];
$catHdrKey = 'mmgp_ctplth-'.$_SERVER['REMOTE_ADDR'];


function get_num_list($url)
{
	global $redis, $catHdrKey;

	$num_list = file_get_contents($url);
	while ($http_response_header[0] == 'HTTP/1.1 429 ') {
		$redis->rPush($catHdrKey, json_encode($http_response_header));
		error_log('rate limit hit; sleeping for 2s then retrying...');
		sleep(2);
		$num_list = file_get_contents($url);
	}
	$redis->rPush($catHdrKey, json_encode($http_response_header));

	return json_decode($num_list, true);
}


if ($redis->exists($resultKey)) {
	$result = $redis->get($resultKey);
} else {
	$headersKey = 'mmgp_headers-'.$_SERVER['REMOTE_ADDR'];

	$url = "https://$mm_user:$mm_token@geoip.maxmind.com/geoip/v2.1/city/".
		$_SERVER['REMOTE_ADDR'];
	$result = file_get_contents($url);

	$redis->set($headersKey, json_encode($http_response_header));
	$redis->set($resultKey, $result);
}

$result = json_decode($result, true);

$num_list = '';

if (empty($num_list) &&
	$result['country']['iso_code'] != 'US' &&
	$result['country']['iso_code'] != 'CA') {

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&areaCode=604";

	$num_list = get_num_list($url);
}

if (empty($num_list) &&
	$result['country']['iso_code'] != 'US' &&
	$result['country']['iso_code'] != 'CA') {

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&state=on";

	$num_list = get_num_list($url);
}

# TODO: if not US/CA and still empty($num_list), find alternative (no ON nums?!)

if (empty($num_list) &&
	$result['country']['iso_code'] == 'US' &&
	array_key_exists('postal', $result) &&
	array_key_exists('code', $result['postal'])) {

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&zip=".$result['postal']['code'];

	$num_list = get_num_list($url);
}

# why, oh why did Catapult have to use "PQ" for Quebec? :P
if (empty($num_list) &&
	array_key_exists('subdivisions', $result) &&
	array_key_exists(0, $result['subdivisions']) &&
	$result['subdivisions'][0]['iso_code'] == 'QC') {

	$result['subdivisions'][0]['iso_code'] = 'PQ';
}

$mm_city = '';
$mm_region = '';
        
if (empty($num_list) &&
	array_key_exists('city', $result) &&
	array_key_exists('names', $result['city']) &&
	array_key_exists('subdivisions', $result) &&
	array_key_exists(0, $result['subdivisions'])) {

	$mm_city = strtolower($result['city']['names']['en']);
	$mm_region = strtolower($result['subdivisions'][0]['iso_code']);

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&city=".urlencode($mm_city).
		"&state=".$mm_region;

	$num_list = get_num_list($url);
}

# TODO: add other city names as appropriate (test larger ones)
if (empty($num_list) &&
	('township of langley' == $mm_city && 'bc' == $mm_region)) {

	$mm_city = 'langley';

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&city=".$mm_city."&state=".
		$mm_region;

	$num_list = get_num_list($url);
}

if (empty($num_list) &&
	('waterloo' == $mm_city && 'on' == $mm_region) ||
	('kitchener' == $mm_city && 'on' == $mm_region)) {

	$mm_city = 'kitchener-waterloo';

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&city=".$mm_city."&state=".
		$mm_region;

	$num_list = get_num_list($url);
}

if (empty($num_list) &&
	('sault ste. marie' == $mm_city && 'on' == $mm_region)) {

	$mm_city = 'sault+ste+marie';

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&city=".$mm_city."&state=".
		$mm_region;

	$num_list = get_num_list($url);
}

if (empty($num_list) &&
	('ajax' == $mm_city && 'on' == $mm_region) ||
	('pickering' == $mm_city && 'on' == $mm_region)) {

	$mm_city = 'ajax+pickering';

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&city=".$mm_city."&state=".
		$mm_region;

	$num_list = get_num_list($url);
}

if (empty($num_list) &&
	('new york' == $mm_city && 'ny' == $mm_region)) {

	$mm_city = 'new+york+city';

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&city=".$mm_city."&state=".
		$mm_region;

	$num_list = get_num_list($url);
}

if (empty($num_list) &&
	('south durham' == $mm_city && 'me' == $mm_region) ||
	('west durham' == $mm_city && 'me' == $mm_region)) {

	$mm_city = 'durham';

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&city=".$mm_city."&state=".
		$mm_region;

	$num_list = get_num_list($url);
}

$npa_result = geoip_record_by_name($_SERVER['REMOTE_ADDR']);

# resulting $npa_result dictionary is like this:
#
#continent_code 	NA
#country_code 	US
#country_code3 	USA
#country_name 	United States
#region 	NJ
#city 	Rutherford
#postal_code 	07070
#latitude 	40.8...
#longitude 	-74.1...
#dma_code 	501
#area_code 	201

if (empty($num_list) &&
	!empty($npa_result['area_code']) && $npa_result['area_code'] != '0') {

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&areaCode=".
		$npa_result['area_code'];

	$num_list = get_num_list($url);
}

if (empty($num_list) &&
	array_key_exists('subdivisions', $result) &&
	array_key_exists(0, $result['subdivisions'])) {

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&state=".
		$result['subdivisions'][0]['iso_code'];

	$num_list = get_num_list($url);
}

# if there are no numbers in user's state, then try finding Massachusetts number
if (empty($num_list) &&
	$result['country']['iso_code'] == 'US') {

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&state=ma";

	$num_list = get_num_list($url);
}

# if there are no numbers in user's province, then try finding Ontario number
if (empty($num_list) &&
	$result['country']['iso_code'] == 'CA') {

	$url = "https://$tuser:$token@api.catapult.inetwork.com/v1/available".
		"Numbers/local?quantity=3000&state=on";

	$num_list = get_num_list($url);
}

$numberKey = 'mmgp_number-'.$_SERVER['REMOTE_ADDR'];

if (empty($num_list)) {
	$redis->set($numberKey, '');

	# TODO: remove hack for notification of no numbers (use different email)
	$time = microtime(true);
	mail($notify_receiver_email,
		'no numbers for '.$_SERVER['REMOTE_ADDR'],
		'country:    '.$result['country']['iso_code']."\n".
		'area code:  '.$npa_result['area_code']."\n".
		"\nemail time: $time");
?>
<p>
(temporarily unavailable; search <a target="_top" href="../register1/">by area
code</a> instead)
</p>
<?php

} else {
	$redis->set($numberKey, $num_list[0]['number']);

	$num_to_print = 5;
	if (array_key_exists('count', $_GET) && intval($_GET['count']) > 0) {
		$num_to_print = intval($_GET['count']);
	}

	function preferred($num)
	{
		return $num['number'][3] == 1 || $num['number'][3] == 0;
	}
	$pref_list = array_filter($num_list, "preferred");

	# use at most half the list 4 pref nums & show at most half of pref list
	$pref_to_print = min(round(count($pref_list) / 2),
		round($num_to_print / 2));

	$pref_keys = array_rand($pref_list, $pref_to_print);
	if (1 == count($pref_keys)) {
		# needed since array_rand() returns non-array if only one result
		$pref_keys = array($pref_keys);
	}

	$other_keys = array_rand($num_list, $num_to_print - $pref_to_print);
	$other_count = count($other_keys);
	if (1 == $other_count) {
		# needed since array_rand() returns non-array if only one result
		$other_keys = array($other_keys);
	}

	# TODO: eventually we need to return a set of (hopefully just one) index
	function is_fancy_neighbour($num)
	{
		global $fancy_area_code_neighbours;

		return array_key_exists(substr($num['number'], 2, 3),
			$fancy_area_code_neighbours);
	}
	$fancyish = array_filter($num_list, "is_fancy_neighbour");

	# TODO: use index from above instead of hard-coding [0] (see above TODO)
	if ($other_count > 0 && !empty($fancyish) && count($fancynums[0]) > 0) {
		$max_tries = 3;
		$tries = 0;
		$fancy_key = 0;
		$params = array();

		do {
			$fancy_key = array_rand($fancynums[0], 1);

			$ur = "https://$tuser:$token@api.catapult.inetwork.com".
				"/v1/users/$user/phoneNumbers/".
				$fancynums[0][$fancy_key];

			$num_result = file_get_contents($ur);
			$params = json_decode($num_result, true);

			++$tries;

		} while ($catapult_application_id == $params["applicationId"] &&
			$tries < $max_tries);

		if ($catapult_application_id != $params["applicationId"]) {
			$num_list[$other_keys[$other_count-1]]["number"] =
				$fancynums[0][$fancy_key];
			$num_list[$other_keys[$other_count-1]]["city" ] =
				'Manhattan';
			$num_list[$other_keys[$other_count-1]]["state"] = 'NY';
			$num_list[$other_keys[$other_count-1]]["nationalNumber"
				] = preg_replace('/^\+1(...)(...)(....)$/',
				'($1) $2-$3', $fancynums[0][$fancy_key]);
		}
	}
?>
<table style="margin-left:auto;margin-right:auto;">
<?php foreach ($pref_keys as $key): ?>
<tr><td style="font-size:1.5rem;"><a style="text-decoration:none;" target="_top"
href="../register/?number=<?php
		echo urlencode($pref_list[$key]["number"]).'&city='.urlencode(
			str_replace(' - ', '-', ucwords(strtolower(str_replace(
				'-', ' - ', $pref_list[$key]["city"])))).
			', '.$pref_list[$key]["state"]);
	?>"><?php echo '+1 '.$pref_list[$key]["nationalNumber"] ?></a></td></tr>
<?php endforeach; ?>
<?php foreach ($other_keys as $key): ?>
<tr><td style="font-size:1.5rem;"><a style="text-decoration:none;" target="_top"
href="../register/?number=<?php
		echo urlencode($num_list[$key]["number"]).'&city='.urlencode(
			str_replace(' - ', '-', ucwords(strtolower(str_replace(
				'-', ' - ', $num_list[$key]["city"])))).
			', '.$num_list[$key]["state"]);
	?>"><?php echo '+1 '.$num_list[$key]["nationalNumber"] ?></a></td></tr>
<?php endforeach; ?>
<tr><td style="font-size:1.5rem;text-align:center;">
<a style="text-decoration:none;" href="num_find.php">...</a>
</td></tr>
</table>
<?php
	$total_time = microtime(true) - $start_time;
	echo "<!-- Took $total_time seconds to load quantity=3000. -->\n";
}
?>
</body>
</html>
