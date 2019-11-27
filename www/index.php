<?php

ini_set('display_errors', 1);

require_once (__DIR__."/../includes/include.inc.php");

config::init();

if (isset($_GET['route'])) {
	$route = explode('/', $_GET['route']);
} else if (isset($argv[2])) {
	$route = explode('/', $argv[2]);
} else {
	@list($request_path, $get) = explode('?', $_SERVER['REQUEST_URI']);
	$route = explode('/', $request_path);
}
switch ($route[1]) {
	case '':
		define ("MAX_RES", 40);
		define ("MAX_LENGTH", 12);

		$stats = new Stats();
		echo $stats->page();
		break;
	case 'conversation':
		require __DIR__."/../includes/tribune.inc.php";

		$id = str_replace('.json', '', $route[2]);

		$tribune = new Tribune();

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: text/json; charset=utf8');
		echo $tribune->conversation_json($id);
		break;
	case 'backend.tsv':
		$nb_posts = 100;
		if (isset($_REQUEST['n'])) {
			$nb_posts = min(5000, $_REQUEST['n']);
		}

		header("Content-Type: text/plain; charset=utf-8");
		$backend = new Backend($nb_posts);
		echo $backend->tsv();
		break;
	case 'backend.xml':
		$nb_posts = 100;
		if (isset($_REQUEST['n'])) {
			$nb_posts = min(5000, $_REQUEST['n']);
		}

		header("Content-Type: application/xml; charset=utf-8");
		$backend = new Backend($nb_posts);
		echo $backend->xml();
		break;
	case 'backend.json':
		$nb_posts = 100;
		if (isset($_REQUEST['n'])) {
			$nb_posts = min(5000, $_REQUEST['n']);
		}

		header("Content-Type: text/json; charset=utf-8");
		$backend = new Backend($nb_posts);
		$backend->json();
		break;
	case 'fortunes':
		if (empty(config::get('fortunes'))) {
			die();
		}

		if (isset($route[2])) switch ($route[2]) {
			case 'par':
				$author = urldecode(implode(" ", array_slice($route, 3)));
				$fortunes = new Fortunes();
				$fortunes->author = $author;
				$fortunes->select();
				echo $fortunes->page(["level" => 2]);
				break;
			case 'avec':
				$actor = urldecode(implode(" ", array_slice($route, 3)));
				$fortunes = new Fortunes();
				$fortunes->actor = $actor;
				$fortunes->select();
				echo $fortunes->page(["level" => 2]);
				break;
			default:
				$fortunes = new Fortunes();
				$fortunes->select();
				echo $fortunes->page(["level" => 1]);
				break;
		} else {
			$fortunes = new Fortunes();
			$fortunes->select();
			echo $fortunes->page(["level" => 1]);
		}
		break;
	case 'fortune':
			$fortunes = new Fortunes();
			$fortunes->id = (int)$route[2];
			$fortunes->select();
			echo $fortunes->page(["level" => 2]);
			break;
		break;
	case 'slip-tester':
			$tester = new Slip_Tester(isset($route[2]) ? (int)$route[2] : 0);
			echo $tester->results();
		break;
	case 'slip':
			if (isset($route[2])) {
				$slips = config::slips();

				if (isset($slips[$route[2]])) {
					$slip = new Slip($slips[$route[2]], $route[2]);
					header("Content-Type: text/plain; charset=utf8");
					echo $slip->source();
				}
			}
		break;
	case 'stats':
			$user_stats = new User_Stats();
			$activity = array();
			foreach (explode(",", $route[2]) as $login) {
				$activity[$login] = $user_stats->hourly_activity(urldecode($login));
			}
			header('Content-type: image/png');
			echo $user_stats->hourly_activity_chart($activity);
		break;
	case 'stats.gif':
			header('Content-type: image/gif');
			echo User_Stats::hourly_activity_gif(explode(",", urldecode($route[2])));
		break;
	default:
		header('HTTP/1.0 404 Not found');
		header('Content-Type: text/plain; charset=utf8');
		echo implode('/', $route);
}

