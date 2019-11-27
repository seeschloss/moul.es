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

	if (is_array($queue) and $n = count($queue)) {
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


		$query = "INSERT INTO ".config::get('stats')['table']." (id, time, info, login, message, totoz, bold, url, prems, deuz, length, url_domain, horloge, naked_url, question, redface, username) VALUES";
		$db = config::get("stats")['db'];

		$parts = array();
		foreach ($queue as $post) {
			$id = $db->escape($post->id);
			$time = $db->escape($post->time);
			$info = $db->escape($post->info);
			$login = $db->escape($post->login);
			$message = $db->escape($post->message);

			$has_totoz = (int)$post->has_totoz();
			$has_bold = (int)$post->has_bold();
			$has_url = (int)$post->has_url();
			$is_prems = (int)$post->is_prems();
			$is_deuz = (int)$post->is_deuz();
			$post_length = mb_strlen($post->message);
			$url_domain = $db->escape($post->url_domain());
			$has_horloge = (int)$post->has_horloge();
			$is_naked_url = (int)$post->is_naked_url();
			$is_question = (int)$post->is_question();
			$has_redface = (int)$post->has_redface();
			$display_username = $db->escape($post->display_username());

			$parts[] = "(
				'$id',
				'$time',
				'$info',
				'$login',
				'$message',
				{$has_totoz},
				{$has_bold},
				{$has_url},
				{$is_prems},
				{$is_deuz},
				{$post_length},
				'{$url_domain}',
				{$has_horloge},
				{$is_naked_url},
				{$is_question},
				{$has_redface},
				'{$display_username}'
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

