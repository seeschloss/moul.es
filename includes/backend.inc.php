<?php

class Backend {
	private $db_result;

	function __construct($nb_posts) {
		$query  = "SELECT id, time, login, info, message";

		if ($nb_posts > 100000) {
			$query .= "  FROM ".config::get("history")['table_realtime'];
		} else {
			$query .= "  FROM ".config::get("history")['table'];
		}
		$query .= "  ORDER BY id DESC";
		$query .= "  LIMIT ".(int)$nb_posts;

		$this->db_result = config::get("history")['db']->query($query);
	}

	function next_post() {
		if ($record = $this->db_result->fetch_assoc()) {
			return new Post($record);
		} else {
			return $record;
		}
	}

	function xml() {
		$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
		$xml .= "<board site=\"".config::get("local_url")."/backend.xml\">\n";

		while ($post = $this->next_post()) {
			$xml .= $post->xml();
		}

		$xml .= "</board>\n";
		return $xml;
	}

	function json() {
		echo "[\n";
		while ($post = $this->next_post()) {
			echo json_encode($post->to_array(), JSON_PRETTY_PRINT).",\n";
			unset($post);
		}

		echo "\n]\n";
	}
}
