<?php

class Stats {
	public $total_posts = null;
	public $oldest_date = null;
	public $elements = 40;

	public function __construct() {
		$this->elements = config::get('elements');
	}

	private function query($query, $function, $db = null) {
		if ($db === null) {
			$db = config::get('history')['db'];
		}

		return $db->query($query, $function);
	}

	private function table($header, $data, $timeref, $limit = null) {
		if ($limit === null) {
			$limit = $this->elements;
		}

		$html = "";

		$html .= "	<table>\n";
		$html .= "	<tr>\n";
		$html .= "		<th>Rang</th>\n";

		foreach ($header as $title => $string) {
			$html .= "		<th class='".$title."'>$title</th>\n";
		}

		$rank = 1;
		foreach ($data as $id => $donnees) {
			$html .= "	<tr>\n";
			$html .= "		<td>".$rank."</td>\n";

			$rank++;

			foreach ($header as $title => $string) {
				foreach ($donnees as $key => $value) {
					if (is_numeric($value)) {
						$value = str_replace('.', ',', $value);
						$value_short = $value;
					} else if (mb_strlen($value) > MAX_LENGTH + 2) {
						$value_short = mb_substr($value, 0, MAX_LENGTH - 2)."<small>...</small>";
						if (substr($value, -1) == '>') {
							$value_short .= substr($value, -4);
						}
					} else {
						$value_short = $value;
					}

					$string = str_replace('<'.$key.'>...', $value_short, $string);
					$string = str_replace('<'.$key.'>', $value, $string);
				}
				$html .= "		<td>".$string."</td>\n";
			}

			$html .= "	</tr>\n";

			if ($rank > $limit) {
				break;
			}
		}

		$html .= "</table>\n";
		if ($timeref > 0) $html .= "<small>En ".str_replace ('.', ',', round (microtime(true)-$timeref, 4))." s</small>\n";

		return $html;
	}

	function totoz() {
		$timeref = microtime(true);

		$query  = "SELECT message ";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE totoz";

		$stats = array();

		$this->query($query, function($row) use (&$stats) {
			$message = $row['message'];
			preg_match_all ("/\[:[a-z0-9 _@\/]*\]/", $message, $matches);
			if (count($matches[0]) < 4) foreach ($matches[0] as $totoz) {
				if (!isset($stats[$totoz])) {
					$stats[$totoz] = array(
						'N' => 0,
						'TOTOZ' => substr($totoz, 2, -1),
					);
				}

				$stats[$totoz]['N']++;
			}
		});

		usort($stats, function($a, $b) {
			return $a['N'] > $b['N'] ? -1 : 1;
		});

		return $this->table(array(
			'Totoz' => '<a href="'.config::get('totoz')['path'].'" class="hfrsmiley" title="[:<TOTOZ>]">'.
			           '[:<TOTOZ>...]<img src="'.config::get('totoz')['path'].'" alt="[:<TOTOZ>]"/></a>',
			'n' => '<N>',
		), $stats, $timeref);
	}

	function url() {
		$timeref = microtime(true);

		$query  = "SELECT username as login, COUNT(message) as messages";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE url";
		$query .= "   AND login NOT IN (".blackliste().") AND username NOT LIKE '%Mozilla/%'";
		$query .= " GROUP BY username";
		$query .= " ORDER BY COUNT(message)";
		$query .= "  DESC LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		});

		return $this->table(array(
			'Login' => '<login>',
			 '<b>[url]</b>' => '<messages>',
		), $stats, $timeref);
	}

	function total_posts() {
		if (!isset($this->total_posts)) {
			$query = "SELECT COUNT(*) as nb FROM ".config::get('history')['table'];
			$this->query($query, function($row) {
				$this->total_posts = (int)$row['nb'];
			});
		}

		return $this->total_posts;
	}

	function oldest_date() {
		if (!isset($this->oldest_date)) {
			$query = "SELECT time FROM ".config::get('history')['table'].
				" ORDER BY time ASC ".
				" LIMIT 1";
			$this->query($query, function($row) {
				$this->oldest_date = Datetime::createFromFormat("YmdHis", $row['time'])->getTimestamp();
			});
		}

		return $this->oldest_date;
	}

	function posts() {
		$timeref = microtime(true);

		$query  = "SELECT username as login, COUNT(message) as messages, ROUND(COUNT(message)/".($this->total_posts()/100).",2) as pct";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE login NOT IN (".blackliste().") AND username NOT LIKE '%Mozilla/%'";
		$query .= " GROUP BY username";
		$query .= " ORDER BY COUNT(message)";
		$query .= "  DESC LIMIT ".$this->elements;


		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		});

		return $this->table(array(
			'Login' => "<a class='stats' title='<login>' href='/stats/<login>'><login>...</a>",
			'Posts' => "<messages>",
			'%' => "<pct>",
		), $stats, $timeref);
	}

	function ta_gueule() {
		$timeref = microtime(true);

		$query  = "SELECT COUNT(*) AS messages, target.login AS login";
		$query .= "  FROM ".config::get('stats')['table']." AS stats";
		$query .= " INNER JOIN ".config::get('stats')['table_answers']." answers";
		$query .= "    ON stats.id = answers.post_source_id";
		$query .= " INNER JOIN ".config::get('stats')['table']." target";
		$query .= "    ON target.id = answers.post_target_id AND target.time >= (stats.time - 1000)";
		$query .= " WHERE stats.message LIKE '%ta gueule%'";
		$query .= "   AND stats.time > '20140101000000'";
		$query .= " GROUP BY target.login";
		$query .= " ORDER BY COUNT(*) DESC";
		$query .= " LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		});

		return $this->table(array(
			'Login' => "<login>",
			'Posts' => "<messages>",
		), $stats, $timeref);
	}

	function casual_posters() {
		$timeref = microtime(true);

		$query  = "SELECT username as login, COUNT(message) as messages, ROUND(COUNT(message)/".($this->total_posts()/100).",2) as pct";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE login NOT IN (".blackliste().") AND username NOT LIKE '%Mozilla/%'";
		$query .= " GROUP BY username";
		$query .= " ORDER BY COUNT(message)";
		$query .= "  ASC LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		});

		return $this->table(array(
			'Login' => "<login>",
			'Posts' => "<messages>",
			'%' => "<pct>",
		), $stats, $timeref);
	}

	function fortunes() {
		$timeref = microtime(true);

		$query  = "SELECT IF(LENGTH(login) > 1,login,CONCAT('<i>',info,'</i>')) as login, count(DISTINCT fortune_id) as messages";
		$query .= "  FROM ".config::get('fortunes')['table'];
		$query .= " WHERE login NOT IN (".blackliste().")";
		$query .= " GROUP BY login";
		$query .= " ORDER BY count(DISTINCT fortune_id)";
		$query .= "  DESC LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		}, config::get('fortunes')['db']);

		return $this->table(array(
			'Login' => '<a class="stats" title="<login>" href="/fortunes/avec/<login>"><login>...</a>',
			'n' => '<messages>',
		), $stats, $timeref);
	}

	function domains() {
		$timeref = microtime(true);

		$query  = "SELECT url_domain, COUNT(message) as messages";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE url=1";
		$query .= " GROUP BY url_domain";
		$query .= " ORDER BY COUNT(message)";
		$query .= "  DESC LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['url_domain'] => $row);
		});

		return $this->table(array(
			'Domaine' => "<url_domain>",
			'Posts' => "<messages>",
		), $stats, $timeref);
	}

	function contextless() {
		$timeref = microtime(true);

		$query  = "SELECT username as login, SUM(NOT horloge) as posts, COUNT(message) as messages, SUM(NOT horloge)/COUNT(message) as ratio";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE login NOT IN (".blackliste().") AND username NOT LIKE '%Mozilla/%'";
		$query .= " GROUP BY username";
		$query .= " HAVING posts > 0 ";
		if ($this->total_posts() > 10000) {
			$query .= " AND COUNT(message)>300 ";
		}
		$query .= " ORDER BY SUM(NOT horloge)/COUNT(message)";
		$query .= "  DESC LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		});

		return $this->table(array(
			'Login' => "<login>",
			'Posts' => "<posts>  <small>/<messages></small>",
			'&tau;' => "<ratio>",
		), $stats, $timeref);
	}

	function misunderstand() {
		$timeref = microtime(true);

		$condition = "message LIKE '%ai rien compri%' OR message LIKE '%autobot%' OR message LIKE '%delarue5%' OR message LIKE '%je comprends rien%' OR message LIKE '%je ne comprends rien%' OR message LIKE '%pas ce que tu veux dire%' OR message LIKE '%s pas pourquoi tu % ça%'";

		$query  = "SELECT username as login, SUM(IF($condition,1,0)) as posts, COUNT(message) as messages, SUM(IF($condition,1,0))/COUNT(message) as ratio";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE login NOT IN (".blackliste().") AND username NOT LIKE '%Mozilla/%'";
		$query .= " GROUP BY username";
		$query .= " HAVING posts > 0 ";
		if ($this->total_posts() > 10000) {
			$query .= " AND COUNT(message)>300 ";
		}
		$query .= " ORDER BY SUM(IF($condition,1,0))/COUNT(message)";
		$query .= "  DESC LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		});

		return $this->table(array(
			'Login' => "<login>",
			'Posts' => "<posts>  <small>/<messages></small>",
			'&tau;' => "<ratio>",
		), $stats, $timeref);
	}

	function cons() {
		$timeref = microtime(true);

		return $this->table(array(
			'Login' => "<login>",
			'Connerie' => "<connerie>",
		), array(array("login" => "zephred", "connerie" => "100%")), $timeref);
	}

	function homophones() {
		$timeref = microtime(true);

		$condition = "message LIKE '%sodo%' OR message LIKE '%encul%'";

		$query  = "SELECT username as login, SUM(IF($condition,1,0)) as posts, COUNT(message) as messages, SUM(IF($condition,1,0))/COUNT(message) as ratio";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE login NOT IN (".blackliste().") AND username NOT LIKE '%Mozilla/%'";
		$query .= " GROUP BY username";
		$query .= " HAVING posts > 0 ";
		if ($this->total_posts() > 10000) {
			$query .= " AND COUNT(message)>300 ";
		}
		$query .= " ORDER BY SUM(IF($condition,1,0))/COUNT(message)";
		$query .= "  DESC LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		});

		return $this->table(array(
			'Login' => "<login>",
			'Posts' => "<posts>  <small>/<messages></small>",
			'&tau;' => "<ratio>",
		), $stats, $timeref);
	}

	function with_totoz() {
		$timeref = microtime(true);

		$query  = "SELECT username as login, SUM(totoz) as posts, COUNT(message) as messages, SUM(totoz)/COUNT(message) as ratio";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE login NOT IN (".blackliste().") AND username NOT LIKE '%Mozilla/%'";
		$query .= " GROUP BY username";
		$query .= " HAVING posts > 0 ";
		if ($this->total_posts() > 10000) {
			$query .= " AND COUNT(message)>300 ";
		}
		$query .= " ORDER BY ratio";
		$query .= "  DESC LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		});

		return $this->table(array(
			'Login' => "<login>",
			'Posts' => "<posts>  <small>/<messages></small>",
			'&tau;' => "<ratio>",
		), $stats, $timeref);
	}

	function bold() {
		$timeref = microtime(true);

		$query  = "SELECT username as login, SUM(bold) as posts, COUNT(message) as messages, SUM(bold)/COUNT(message) as ratio";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE login NOT IN (".blackliste().") AND username NOT LIKE '%Mozilla/%'";
		$query .= " GROUP BY username";
		$query .= " HAVING posts > 0 ";
		if ($this->total_posts() > 10000) {
			$query .= " AND COUNT(message)>300 ";
		}
		$query .= " ORDER BY ratio";
		$query .= "  DESC LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		});

		return $this->table(array(
			'Login' => "<login>",
			'Posts' => "<posts>  <small>/<messages></small>",
			'&tau;' => "<ratio>",
		), $stats, $timeref);
	}

	function average_length() {
		$timeref = microtime(true);

		$query  = "SELECT username as login, AVG(length) as average, COUNT(message) as N";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE login NOT IN (".blackliste().") AND username NOT LIKE '%Mozilla/%'";
		$query .= " GROUP BY username";
		if ($this->total_posts() > 10000) {
			$query .= " HAVING COUNT(message)>300 ";
		}
		$query .= " ORDER BY AVG(length)";
		$query .= "  DESC LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		});

		return $this->table(array(
			'Login' => "<a class='stats' title='<login>' href='/stats/<login>'><login>...</a>",
			'Taille moyenne' => "<average>",
			'Posts' => "<N>",
		), $stats, $timeref);
	}

	function questions() {
		$timeref = microtime(true);

		$query  = "SELECT username as login, SUM(question) as posts, COUNT(message) as messages, SUM(question)/COUNT(message) as ratio";
		$query .= "  FROM ".config::get('history')['table'];
		$query .= " WHERE login NOT IN (".blackliste().") AND username NOT LIKE '%Mozilla/%'";
		$query .= " GROUP BY username";
		$query .= " HAVING posts > 0 ";
		if ($this->total_posts() > 10000) {
			$query .= " AND COUNT(message)>300 ";
		}
		$query .= " ORDER BY SUM(question)/COUNT(message)";
		$query .= "  DESC LIMIT ".$this->elements;

		$stats = $this->query($query, function($row) {
			return array($row['login'] => $row);
		});

		return $this->table(array(
			'Login' => "<login>",
			'Posts' => "<posts>  <small>/<messages></small>",
			'&tau;' => "<ratio>",
		), $stats, $timeref);
	}

	function make_table($table) {
		$html = "<table id='main'>";

		$groups = array_chunk($table, 5, true);
		foreach ($groups as $group) {
			$html .= "<tr>";
			foreach (array_keys($group) as $title) {
				$html .= "<th>".$title."</th>";
			}
			$html .= "</tr>";

			$html .= "<tr>";
			foreach (array_values($group) as $data) {
				$html .= "<td valign='top' class='tableau'>".$data."</td>";
			}
			$html .= "</tr>";
		}

		$html .= "</table>";

		return $html;
	}

	function page() {
		$html = head("Statistiques de la tribune");

		$title = config::get('title');
		$url = config::get('url');

		$oldest_date = date('d/m/Y à H:i:s', $this->oldest_date());

		$stats = array(
			"Longueur moyenne des posts"			=> $this->average_length(),
			"Nombre d'<b>[url]</b>"					=> $this->url(),
			"Lourdeur (posts avec du gras)"			=> $this->bold(),
			"Gros posteurs"							=> $this->posts(),
			"Posteurs occasionnels"					=> $this->casual_posters(),
			"Posts avec [:totoz]"					=> $this->with_totoz(),
		);

		if (config::get("fortunes")) {
			$stats += array(
				"Apparition dans les fortunes"			=> $this->fortunes(),
			);
		}

		$stats += array(
			"Fréquence d'utilisation des totoz"		=> $this->totoz(),
			"Domaines des url"						=> $this->domains(),
			"Posts sans horloge"					=> $this->contextless(),
			"Posts avec questions"					=> $this->questions(),
			"Malcomprenants"						=> $this->misunderstand(),
			"Ceux qui devraient se taire"			=> $this->ta_gueule(),
		);

		$html .= <<<HTML
<p>
<a href='/fortunes/'>Fortunes</a> - <a href='https://sauf.ca'>Images postées sur la tribune</a>
</p>
<p><a href="{$url}">{$title}</a></p>


		<h1>Quelques statistiques sur les moules (sur les {$this->total_posts()} derniers posts, depuis le {$oldest_date})</h1>
		<script src="/jquery.js" type="text/javascript"></script>
		<script src="/totoz.js" type="text/javascript"></script>
HTML;

		$html .= $this->make_table($stats);

		$html .= footer();

		return $html;
	}
}
