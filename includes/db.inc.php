<?php

class db extends MySQLi {
	function __construct($host, $user, $pass, $database) {
		parent::__construct($host, $user, $pass, $database);
		$this->set_charset('utf8mb4');

		parent::query("SET time_zone = 'Europe/Paris'");
	}

	public function query($query, $function = NULL) {
		$r = parent::query($query);

		if (!$r) {
			echo $this->error;
			return null;
		}

		if (is_callable($function)) {
			$results = array();

			while ($row = $r->fetch_assoc ()) {
				$result = $function($row);
				if (is_array($result)) {
					$results = array_merge($results, $result);
				}
			}

			return $results;
		} else {
			return $r;
		}
	}

	public function escape($string) {
		return parent::real_escape_string($string);
	}
}
