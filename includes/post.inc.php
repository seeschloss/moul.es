<?php

class Post {
	static function get($id, $time = NULL) {
		$query  = "SELECT id, time, login, info, message";
		if ($time) {
			$query .= "  FROM ".config::get("history")['table_realtime'];
		} else {
			$query .= "  FROM ".config::get("history")['table'];
		}
		$query .= "  WHERE id=".(int)$id;
		if ($time) {
			$query .= " AND time='".str_replace("'", "\\'", $time)."'";
		}
		$query .= "  ORDER BY time DESC";
		$query .= "  LIMIT 1";

		$result = config::get("history")['db']->query($query);
		if ($row = $result->fetch_assoc()) {
			return new Post($row);
		} else {
			return null;
		}
	}

	static function get_by_clock($clock, $reference_post) {
		$max_id_difference   = 1000;
		$max_time_difference = "-12 hours";

		$limit_deb = DateTime::createFromFormat('YmdHis', $reference_post->time);
		$limit_fin = DateTime::createFromFormat('YmdHis', $reference_post->time);
		$limit_deb->modify($max_time_difference);

		$time = substr($clock, 0, 2).substr($clock, 3, 2).substr($clock, 6, 2);

		$query  = "SELECT * FROM ".config::get('archive')['table'];
		$query .= " WHERE time > '".$limit_deb->format('YmdHis')."'";
		$query .= "   AND time < '".$limit_fin->format('YmdHis')."'";
		$query .= "   AND id < ".($reference_post->id);
		$query .= "   AND id > ".($reference_post->id - $max_id_difference);
		$query .= "   AND SUBSTR(time FROM 9 FOR 6) = '".$time."'";
		$query .= " ORDER BY id ASC";

		$posts = array();
		$r = config::get('archive')['db']->query($query);
		if ($r) while ($row = $r->fetch_assoc()) {
			$posts[] = $row;
		} else {
			return NULL;
		}

		if (count($posts) == 0) {
			return NULL;
		}

		if (mb_strlen($clock, 'UTF-8') == 8) {
			return new Post($posts[0]);
		} else if (mb_strlen($clock, 'UTF-8') > 9 and $clock[8] == ':') {
			$index = mb_substr($clock, 9);

			if (count($posts) > $index - 1) {
				return new Post($posts[$index - 1]);
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
					return new Post($posts[$int_index]);
				}
			}

			return NULL;
		}
	}

	function __construct($record) {
		$this->id = $record['id'];
		$this->time = $record['time'];
		$this->login = $record['login'];
		$this->info = $record['info'];
		$this->message = $record['message'];
	}

	function clock() {
		$time = $this->time;

		$year = substr($time, 0, 4);
		$month = substr($time, 4, 2);
		$day = substr($time, 6, 2);
		$hour = substr($time, 8, 2);
		$minute = substr($time, 10, 2);
		$second = substr($time, 12, 2);

		return $hour.":".$minute.":".$second;
	}

	function datetime() {
		if (!isset($this->datetime)) {
			$this->datetime = DateTime::createFromFormat("YmdHis", $this->time);
		}

		return $this->datetime;
	}

	function answers() {
		$posts = array();

		$query  = "SELECT id, time, login, info, message";
		$query .= "  FROM ".config::get("history")['table_realtime'];
		$query .= "  WHERE id BETWEEN ".(int)$this->id." AND ".(int)$this->id." + 100";
		$query .= "    AND time > 20110219191154";
		$query .= "    AND (message LIKE '%".$this->clock()."%')";
		$query .= " ORDER BY id ASC";

		$result = config::get("history")['db']->query($query);
		while ($row = $result->fetch_assoc()) {
			$posts[] = new Post($row);
		}

		return $posts;
	}
	
	function to_array() {
		return array(
			'id' => $this->id,
			'time' => $this->time,
			'login' => $this->login,
			'info' => $this->info,
			'message' => $this->message,
		);
	}

	function xml() {
		$message = htmlspecialchars($this->message);
		return <<<XML
	<post id="{$this->id}" time="{$this->time}">
		<info>{$this->info}</info>
		<message>{$message}</message>
		<login>{$this->login}</login>
	</post>

XML;
	}

	function has_totoz() {
		return !!preg_match("/\[:.*\]/", $this->message);
	}

	function has_bold() {
		return strpos($this->message, "<b>") !== FALSE;
	}

	function has_url() {
		return strpos($this->message, "<a href=") !== FALSE;
	}

	function is_prems() {
		return !!preg_match("/000000$/", $this->time);
	}

	function is_deuz() {
		return !!preg_match("/000001$/", $this->time);
	}

	function url_domain() {
		$matches = array();
		preg_match("@https?://([^/ &'\"]*)@", $this->message, $matches);

		if (isset($matches[1])) {
			return $matches[1];
		} else {
			return "";
		}
	}

	function has_horloge() {
		return !!preg_match("/[0-2][0-9]:[0-5][0-9]/", $this->message);
	}

	function is_naked_url() {
		return !!preg_match("@^<a href.*/a>$@", $this->message);
	}

	function is_question() {
		return !!preg_match("@\?$$@", $this->message);
	}

	function display_username() {
		if ($this->login) {
			return $this->login;
		} else {
			return "<i>".$this->info."</i>";
		}
	}

	function clocks() {
		$matches = array();

		preg_match_all(
			'/[0-2][0-9]:[0-5][0-9]:[0-5][0-9]((:[0-9])|[¹²³⁴⁵⁶⁷⁸⁹])?/u',
			$this->message,
			$matches,
			PREG_PATTERN_ORDER
		);

		return $matches[0];
	}
}

