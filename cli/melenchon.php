<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

$test = false;
$silent = false;

$tribune = NULL;

if ($argc > 1) foreach ($argv as $arg) {
	switch ($arg) {
		case '--test':
			$test = true;
			break;
		case '--silent':
			$silent = true;
			break;
		default:
			$tribune = $arg;
	}
}

require_once __DIR__."/../includes/include.inc.php";

if (!config::init($tribune)) {
	echo "No such tribune: {$tribune}\n";
	exit();
}

$f = fopen('php://stdin', 'r');

while ($line = fgets($f)) {
	$post = explode("\t", trim($line));
	$post_id = $post[0];
	$date = $post[1];
	$user_info = $post[2];
	$user_name = $post[3];
	$message = $post[4];

	if ($user_name == "gle") {
		continue;
	}

	$aloy_totozes = [
		'',
		'[:aloy]',
		'[:aloyd]',
		'[:aloy2]',
		'[:cerveau aloy]',
		'[:vouzico]',
		'[:vyse_drake]',
		'[:cbrs]',
		'[:angefox]',
		'[:raziel-92]',
		'[:bool_de_gom]',
		'[:ozon94]',
		'[:axelazerty]',
		'[:dawgyg]',
	];

	$totoz = $aloy_totozes[rand(0, count($aloy_totozes) - 1)];

	if (preg_match('@http://[^ ]*melenchon@', $message)) {
		continue;
	} else if (preg_match('@http://[^ ]*melanchon@', $message)) {
		continue;
	} else if (strpos($message, 'melanchon') !== FALSE) {
		post($date, "M<b>é</b>l<b>e</b>nchon {$totoz}");
	} else if (strpos($message, 'mélanchon') !== FALSE) {
		post($date, "Mél<b>e</b>nchon {$totoz}");
	} else if (strpos($message, 'Mélanchon') !== FALSE) {
		post($date, "Mél<b>e</b>nchon {$totoz}");
	} else if (strpos($message, 'Melanchon') !== FALSE) {
		post($date, "M<b>é</b>l<b>e</b>nchon {$totoz}");
	} else if (strpos($message, 'Melenchon') !== FALSE) {
		post($date, "M<b>é</b>lenchon {$totoz}");
	}
}

function post($time, $message) {
	global $test;
	global $silent;

	if ($silent) {
		return;
	}

	$post_time = DateTime::createFromFormat('YmdHis', $time);
	$clock = $post_time->format("H:i:s");

	if ($test) {
		echo "Would have posted '$clock $message'\n";
		return;
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, config::get('backend')['post_url']);
	curl_setopt($ch, CURLOPT_POSTFIELDS, array(config::get('backend')['post_fields'] => $clock.' '.$message));
	curl_setopt($ch, CURLOPT_COOKIE, config::get('backend')['cookie']);
	curl_setopt($ch, CURLOPT_REFERER, config::get('backend')['referer']);
	curl_setopt($ch, CURLOPT_USERAGENT, "Melenchon bot");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_exec($ch);
}
