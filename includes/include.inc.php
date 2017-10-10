<?php

date_default_timezone_set ("Europe/Paris");

require_once (__DIR__."/config.inc.php");

define('table_posts_full', "dlfp");
define('table_posts', "dlfp_mem");
define('table_fortunes', "dlfp_fortunes");
$trends = "DLFPTrends";
$khapin = "http://khapin.ssz.fr/dlfp";
$nom = "<a href='http://linuxfr.org/board'>Da French Bouchot</a>";

require_once dirname(__FILE__)."/post.inc.php";
require_once dirname(__FILE__)."/backend.inc.php";
require_once dirname(__FILE__)."/fortune.inc.php";
require_once dirname(__FILE__)."/stats_tribune.inc.php";
require_once dirname(__FILE__)."/user_stats.inc.php";
require_once dirname(__FILE__)."/slip_tester.inc.php";

function blackliste ()
	{
	return "'pendu'";
	}

function print_select ($title, $from, $to, $selected)
	{
	print "<select name='$title'>\n";

	for ($i = $from ; $i <= $to ; $i++)
		{
		print "<option";

		if ($i == $selected)
			{
			print " selected='selected'";
			}

		print ">";
		print str_pad ($i, 2, "0", STR_PAD_LEFT);
		print "</option>\n";
		}

	print "</select>";
	}

function head ($title = "")
	{
	header ("Content-Type: text/html; Charset=UTF-8");

	return <<<EOT
<!DOCTYPE html>
<html>
	<head>
		<title>$title</title>
		<link rel='stylesheet' type='text/css' href='/style.css' />
		<link rel="stylesheet" id="csspersolink" type="text/css" href="/dlfp.css">
	</head>
	<body>
EOT;
	}

function footer ()
	{
	return <<<EOT
	</body>
</html>
EOT;
	}


?>
