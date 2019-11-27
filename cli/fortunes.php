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
	exit(1);
}

$f = fopen('php://stdin', 'r');

while ($line = fgets($f)) {
	$post = explode("\t", trim($line));
	$post_id = $post[0];
	$date = $post[1];
	$user_info = $post[2];
	$user_name = $post[3];
	$message = $post[4];

	$passes = 0;

	if (strpos($message, '#fortune') !== FALSE) {
loop_start:
		// Intervale
		if (preg_match('/#fortune *([0-2][0-9]:[0-5][0-9]:[0-5][0-9]((:[0-9])|[¹²³⁴⁵⁶⁷⁸⁹])?)-([0-2][0-9]:[0-5][0-9]:[0-5][0-9]((:[0-9])|[¹²³⁴⁵⁶⁷⁸⁹])?)/u', $message, $matches)) {
			$clocks = get_clocks($message);
			if (count($clocks) != 2) {
				continue;
			}

			$tries = 0;
			while (!($reference_post = Post::get($post_id, $date)) && $tries < 10) {
				sleep(1);
				$tries++;
			}
			if (!$reference_post) {
				fprintf(STDERR, "#fortune post (%s) not yet archived!\n", $post_id);
				exit();
			}

			$post1 = Post::get_by_clock($clocks[0], $reference_post);
			$post2 = Post::get_by_clock($clocks[1], $reference_post);

			if ($post2->id < $post1->id) {
				$a = $post2;
				$post2 = $post1;
				$post1 = $a;
			}

			if ($post2->id - $post1->id > 20) {
				// trop long
			}

			if ($post2->id - $post1->id < 2) {
				// trop court
			}

			$query  = "SELECT id FROM ".config::get('fortunes')['history_table'];
			$query .= " WHERE time < '".$date."'";
			$query .= "   AND time > ".($date - 240000);
			$query .= "   AND id >= ".$post1->id;
			$query .= "   AND id <= ".$post2->id;
			$query .= " ORDER BY id ASC";

			$posts = array();
			$r = config::get('fortunes')['db']->query($query);
			if ($r) while ($row = $r->fetch_assoc()) {
				$posts[] = Post::get($row['id']);
			}

			if (count($posts) >= 2 and count($posts) <= 20) {
				$first_author = $posts[0]->display_username();;
				$last_author = $posts[count($posts) - 1]->display_username();

				$fortune_author = $reference_post->display_username();

				if ($first_author == $fortune_author or $last_author == $fortune_author) {
					post($date, "C'est une autofortune ça, ça se fait pas [:mareek]");
				} else {
					$fortune_id = save_fortune($reference_post, $posts);
					post($date, config::get('fortunes')['prefix'].$fortune_id);
				}
			} else if (count($posts) > 20) {
				post($date, "Pas plus de vingt posts par fortune [:kiki]");
			}

		// Liste
		} else if (preg_match('/#fortune *([0-2][0-9]:[0-5][0-9]:[0-5][0-9]((:[0-9])|[¹²³⁴⁵⁶⁷⁸⁹])?)( +[0-2][0-9]:[0-5][0-9]:[0-5][0-9]((:[0-9])|[¹²³⁴⁵⁶⁷⁸⁹])?)+/u', $message, $matches)) {
			$clocks = get_clocks($message);

			$tries = 0;
			while (!($reference_post = Post::get($post_id, $date)) && $tries < 10) {
				sleep(1);
				$tries++;
			}
			if (!$reference_post) {
				fprintf(STDERR, "#fortune post (%s) not yet archived!\n", $post_id);
				exit();
			}

			$posts = array();
			foreach ($clocks as $clock) {
				if ($post = Post::get_by_clock($clock, $reference_post)) {
					$posts[] = $post;
				} else if ($passes < 2) {
					$passes++;
					sleep(1);
					goto loop_start;
				} else {
					post($date, "Je n'arrive pas à trouver le post ".$clock." [:uxam]");
					goto loop_end;
				}
			}

			if (count($posts) >= 1 and count($posts) <= 20) {
				$first_author = $posts[0]->display_username();;
				$last_author = $posts[count($posts) - 1]->display_username();

				$fortune_author = $reference_post->display_username();

				if ($first_author == $fortune_author or $last_author == $fortune_author) {
					post($date, "C'est une autofortune ça, ça se fait pas [:mareek]");
				} else {
					$fortune_id = save_fortune($reference_post, $posts);
					post($date, config::get('fortunes')['prefix'].$fortune_id);
				}
			} else if (count($posts) > 20) {
				post($date, "Pas plus de vingt posts par fortune [:kiki]");
			}

		// Une seule horloge
		} else if (preg_match('/#fortune *([0-2][0-9]:[0-5][0-9]:[0-5][0-9]((:[0-9])|[¹²³⁴⁵⁶⁷⁸⁹])?)/u', $message, $matches)) {
			$clocks = get_clocks($message);

			$tries = 0;
			while (!($reference_post = Post::get($post_id, $date)) && $tries < 10) {
				sleep(1);
				$tries++;
			}
			if (!$reference_post) {
				fprintf(STDERR, "#fortune post (%s) not yet archived!\n", $post_id);
				exit();
			}

			$posts = array();
			foreach ($clocks as $clock) {
				if ($post = Post::get_by_clock($clock, $reference_post)) {
					$posts[] = $post;
				} else if ($passes < 2) {
					$passes++;
					sleep(1);
					goto loop_start;
				} else {
					post($date, "Je n'arrive pas à trouver le post ".$clock." [:uxam]");
					goto loop_end;
				}
			}

			if (count($posts) >= 1 and count($posts) <= 20) {
				$first_author = $posts[0]->display_username();;
				$last_author = $posts[count($posts) - 1]->display_username();

				$fortune_author = $reference_post->display_username();

				if ($first_author == $fortune_author or $last_author == $fortune_author) {
					post($date, "C'est une autofortune ça, ça se fait pas [:mareek]");
				} else {
					$fortune_id = save_fortune($reference_post, $posts);
					post($date, config::get('fortunes')['prefix'].$fortune_id);
				}
			} else if (count($posts) > 20) {
				post($date, "Pas plus de vingt posts par fortune [:kiki]");
			}
		} else {
			// problème de syntaxe
		}
	}
loop_end:
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
	curl_setopt($ch, CURLOPT_REFERER, config::get('backend')['referer']);
	curl_setopt($ch, CURLOPT_USERAGENT, "Fortunes bot");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if (isset(config::get('backend')['cookie'])) {
		curl_setopt($ch, CURLOPT_COOKIE, config::get('backend')['cookie']);
	}

	curl_exec($ch);
}

/*

		SET CHARSET 'latin1'; SET NAMES 'utf8';
		INSERT IGNORE INTO tribunes_stats.fortunes_dlfp
		   (fortune_id, id, time, info, login, message, fortune_login, fortune_time, fortune_info, fortune_post_id, fortune_message)
		      (SELECT fortune_no, post_id, DATE_FORMAT(post_time, '%Y%m%d%H%i%s'), CONVERT(CAST(CONVERT(info USING latin1) AS BINARY) USING utf8),
			   CONVERT(CAST(CONVERT(login USING latin1) AS BINARY) USING utf8), CONVERT(CAST(CONVERT(message USING latin1) AS BINARY) USING utf8),
			    CONVERT(CAST(CONVERT(fortune_login USING latin1) AS BINARY) USING utf8), 
				DATE_FORMAT(fortune_time, '%Y%m%d%H%i%s'), '', DATE_FORMAT(fortune_time, '%Y%m%d%H%i%s'), '' FROM khapin.dlfp_fortunes)

 */

function save_fortune($fortune_post, $posts) {
	global $test;

	if ($test) {
		foreach ($posts as &$post) {
			echo "- ".$post->time." #".$post->id." ".$post->login."> ".$post->message."\n";
		}
	}

	$fortune_login = config::get('fortunes')['db']->real_escape_string($fortune_post->login);
	$fortune_time = config::get('fortunes')['db']->real_escape_string($fortune_post->time);
	$fortune_info = config::get('fortunes')['db']->real_escape_string($fortune_post->info);
	$fortune_post_id = config::get('fortunes')['db']->real_escape_string($fortune_post->id);
	$fortune_message = config::get('fortunes')['db']->real_escape_string($fortune_post->message);

	$query = "SELECT fortune_id FROM ".config::get('fortunes')['table']." ORDER BY fortune_id DESC LIMIT 1";
	$r = config::get('fortunes')['db']->query($query);
	if ($r and $row = $r->fetch_assoc()) {
		$fortune_id = $row['fortune_id'] + 1;
	} else {
		$fortune_id = 1;
	}

	foreach ($posts as $post) {
		$post_id = config::get('fortunes')['db']->real_escape_string($post->id);
		$post_time = config::get('fortunes')['db']->real_escape_string($post->time);
		$post_info = config::get('fortunes')['db']->real_escape_string($post->info);
		$post_login = config::get('fortunes')['db']->real_escape_string($post->login);
		$post_message = config::get('fortunes')['db']->real_escape_string($post->message);

		$table = config::get('fortunes')['table'];

		$query = <<<SQL
		INSERT INTO {$table} (fortune_id, id, time, info, login, message, fortune_login, fortune_time, fortune_info, fortune_post_id, fortune_message)
		VALUES ('$fortune_id', '$post_id', '$post_time', '$post_info', '$post_login', '$post_message', '$fortune_login', '$fortune_time', '$fortune_info', '$fortune_post_id', '$fortune_message'
		);
SQL;

		if ($test) {
			echo $query."\n";
		} else {
			config::get('fortunes')['db']->query($query);
		}
	}

	return $fortune_id;
}

function get_post($clock, $limit_date, $limit_id) {
	global $test, $tribune;

	$limit_deb = DateTime::createFromFormat('YmdHis', $limit_date);
	$limit_fin = DateTime::createFromFormat('YmdHis', $limit_date);
	$limit_deb->modify("-12 hours");

	$time = substr($clock, 0, 2).substr($clock, 3, 2).substr($clock, 6, 2);

	$query  = "SELECT * FROM ".config::get('fortunes')['history_table'];
	$query .= " WHERE time > '".$limit_deb->format('YmdHis')."'";
	$query .= "   AND time < '".$limit_fin->format('YmdHis')."'";
	$query .= "   AND id < ".($limit_id);
	$query .= "   AND id > ".($limit_id - 1000);
	$query .= "   AND SUBSTR(time FROM 9 FOR 6) = '".$time."'";
	$query .= " ORDER BY id ASC";

	$posts = array();
	$r = config::get('fortunes')['db']->query($query);
	if ($r) while ($row = $r->fetch_assoc()) {
		$posts[] = $row;
	} else {
		if ($test) {
			echo "MySQL error looking for $clock:\n";
			echo mysqli_error(config::get('fortunes')['db']);
		}
		return 0;
	}

	if (count($posts) == 0) {
		if ($test) {
			echo "No post for $clock\nQuery was:\n$query\n";
		}
		return 0;
	}

	if (mb_strlen($clock, 'UTF-8') == 8) {
		return $posts[0];
	} else if (mb_strlen($clock, 'UTF-8') > 9 and $clock[8] == ':') {
		$index = mb_substr($clock, 9);

		if (count($posts) > $index - 1) {
			return $posts[$index - 1];
		}

		return 0;
	} else if (mb_strlen($clock, 'UTF-8') > 8) {
		$index = mb_substr($clock, 8);

		$indices = array(
			'¹' => 0,
			'²' => 1,
			'³' => 2,
			'⁴' => 3,
			'⁵' => 4,
			'⁶' => 5,
			'⁷' => 6,
			'⁸' => 7,
			'⁹' => 8,
		);

		if (isset($indices[$index])) {
			$int_index = $indices[$index];

			if (count($posts) > $int_index) {
				return $posts[$int_index];
			}
		}

		return 0;
	}
}

function get_clocks($message) {
	global $test;

	$offset = strpos($message, '#fortune');

	preg_match_all('/[0-2][0-9]:[0-5][0-9]:[0-5][0-9]((:[0-9])|[¹²³⁴⁵⁶⁷⁸⁹])?/u', $message, $matches, PREG_PATTERN_ORDER, $offset);

	if ($test) {
		echo "Clocks in '$message': ".join(', ', $matches[0])."\n";
	}

	return $matches[0];
}

