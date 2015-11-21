<?php
ini_set('display_errors', 1);

require_once (__DIR__."/../includes/include.inc.php");

$tribune = NULL;

$test = false;

if ($argc > 1) foreach ($argv as $arg) {
	switch ($arg) {
		case '--test':
			$test = true;
			break;
		default:
			$tribune = $arg;
	}
}

if (!config::init($tribune)) {
	exit();
}

$f = fopen('php://stdin', 'r');

while ($line = fgets($f)) {
	$fields = explode("\t", trim($line));

	$post = new Post(array(
		'id'      => $fields[0],
		'time'    => $fields[1],
		'info'    => $fields[2],
		'login'   => $fields[3],
		'message' => $fields[4],
	));

	enqueue($post);
}

flush_queue();

function enqueue($post) {
	global $queue;

	if (!is_array($queue)) {
		$queue = array();
	}

	$queue[] = $post;

	if (count($queue) >= 25) {
		flush_queue();
	}
}

function flush_queue() {
	global $queue;
	global $test;

	if ($n = count($queue)) {
		if ($test) {
			echo "Flushing queue ($n posts)...\n";
		}

		$query = "INSERT INTO ".config::get('archive')['table']." (id, time, info, login, message) VALUES";
		$db = config::get("archive")['db'];

		$parts = array();
		foreach ($queue as $post) {
			$id = $db->escape($post->id);
			$time = $db->escape($post->time);
			$info = $db->escape($post->info);
			$login = $db->escape($post->login);
			$message = $db->escape($post->message);

			$parts[] = "('$id', '$time', '$info', '$login', '$message')";
		}

		$query .= implode(', ', $parts);

		if ($test) {
			echo $query;
		} else {
			$db->query($query);
		}


		$query = "INSERT INTO ".config::get('stats')['table']." (id, time, info, login, message, totoz, bold, url, prems, deuz, length, url_domain, horloge, naked_url, question, username) VALUES";
		$db = config::get("stats")['db'];

		$parts = array();
		foreach ($queue as $post) {
			$id = $db->escape($post->id);
			$time = $db->escape($post->time);
			$info = $db->escape($post->info);
			$login = $db->escape($post->login);
			$message = $db->escape($post->message);

			$parts[] = "(
				'$id',
				'$time',
				'$info',
				'$login',
				'$message',
				'{$post->has_totoz()}',
				'{$post->has_bold()}',
				'{$post->has_url()}',
				'{$post->is_prems()}',
				'{$post->is_deuz()}',
				'".mb_strlen($post->message)."',
				'{$db->escape($post->url_domain())}',
				'{$post->has_horloge()}',
				'{$post->is_naked_url()}',
				'{$post->is_question()}',
				'{$post->display_username()}'
			)";

			if (strpos(mb_strtolower($post->message), "ta gueule") !== FALSE) {
				$clocks = $post->clocks();
				foreach ($clocks as $clock) {
					$target_post = Post::get_by_clock($clock, $post);

					if ($target_post) {
						$query_ta_gueule = "
							UPDATE ".config::get('stats')['table']."
							   SET ta_gueule_answer = ".(int)$post->id."
							WHERE id = ".(int)$target_post->id."
							  AND time = '".$db->escape($target_post->time)."'";
						$db->query($query_ta_gueule);
					}
				}
			}
		}

		$query .= implode(', ', $parts);

		if ($test) {
			echo strtr($query, array("\t" => "", "\n" => ""))."\n";
		} else {
			$db->query($query);
		}

		if (isset(config::get('stats')['table_answers'])) {
			if ($test) {
				echo "Updating post relations...\n";
			}

			$query = "INSERT INTO ".config::get('stats')['table_answers']." (post_source_id, post_source_time, post_target_id, post_source_clock_id, post_source_clock) VALUES";
			$db = config::get("stats")['db'];

			$parts = array();
			foreach ($queue as $post) {
				foreach ($post->clocks() as $clock_id => $clock) {
					if ($post_target = Post::get_by_clock($clock, $post)) {
						$parts[] = "(
							{$post->id},
							'{$db->escape($post->time)}',
							{$post_target->id},
							{$clock_id},
							'{$db->escape($clock)}'
						)";
					}
				}
			}

			if (count($parts)) {
				$query .= implode(', ', $parts);

				if ($test) {
					echo strtr($query, array("\t" => '\t', "\n" => '\n'))."\n";
				} else {
					$db->query($query);
				}
			}
		}
	}

	$queue = array();
}

