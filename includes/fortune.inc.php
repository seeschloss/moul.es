<?php

class Fortunes {
	public $author = null;
	public $actor = null;
	public $id = null;

	public $limit = 40;

	public $fortunes = array();

	public function select() {
		$query = "SELECT DISTINCT fortune_id, fortune_time, fortune_login, fortune_message FROM ".config::get('fortunes')['table']." WHERE 1";
		if (isset($this->author))
			{
			$query .= " AND fortune_login='".config::get('fortunes')['db']->real_escape_string($this->author)."'";
			}
		if (isset($this->actor))
			{
			$query .= " AND login='".config::get('fortunes')['db']->real_escape_string($this->actor)."'";
			}
		if (isset($this->id))
			{
			$query .= " AND fortune_id=".(int)$this->id;
			}
		$query .= " ORDER BY fortune_id DESC";
		if (isset($this->limit))
			{
			$query .= " LIMIT ".(int)$this->limit;
			}

		$this->fortunes = config::get('fortunes')['db']->query($query, function($row) {
			$fortune = new Fortune($row['fortune_id']);
			$fortune->load();

			$tz = new DateTimeZone("Europe/Paris");
			$time = DateTime::createFromFormat('YmdHis', $row['fortune_time']);
			$time->setTimeZone ($tz);
			$fortune->time = $time->format("d/m/Y à H:i:s");
			$fortune->author = $row['fortune_login'];
			$fortune->message = $row['fortune_message'];

			return array($row['fortune_id'] => $fortune);
		});
	}

	private function page_header($level) {
		$title = "Toutes les fortunes";
		$extra = array();
		if ($this->author) {
			$extra[] = "par ".$this->author;
		}
		if ($this->actor) {
			$extra[] = "avec ".$this->actor;
		}

		if (count($extra)) {
			$title .= " " . htmlspecialchars(join(", ", $extra));
		}

		if ($this->id) {
			$title = "Fortune #".$this->id;
		}

		$html = head($title." - ".config::get('title'));
		$html .= <<<HTML
			<script src="/board.js" type="text/javascript"></script>
HTML;

		$html .= <<<HTML
			<div class="title">{$title}</div>
HTML;

		switch ($level) {
			case 1:
				$html .= <<<HTML
	<p>Pour voir les fortunes contenant des posts d'une moule, allez sur <a href='/fortunes/avec/login'>/fortunes/avec/<em>login</em></a>.</p>
	<p>Pour voir les fortunes créées par une moule, allez sur <a href='/fortunes/par/login'>/fortunes/par/<em>login</em></a>.</p>
HTML;
				break;
			case 2:
				$html .= <<<HTML
			<p><a href='/fortunes'>&lt; dernière fortunes</a></p>
HTML;
				break;
		}

		return $html;
	}

	public function page($options = array()) {
		$title = "Fortunes";
		$extra = array();
		if ($this->author) {
			$extra[] = "par ".$this->author;
		}
		if ($this->actor) {
			$extra[] = "avec ".$this->actor;
		}

		if (count($extra)) {
			$title .= " " . htmlspecialchars(join(", ", $extra));
		}

		$html = $this->page_header(empty($options['level']) ? 1 : $options['level']);
		$html .= <<<EOT
			<div class="boardindex">
EOT;

		$html .= $this->show();

		$html .= <<<EOT
			<script type="text/javascript">setTimeout("analyzePost(1);init()", 20)</script>
			</div>
EOT;

		$html .= footer();

		return $html;
	}

	public function show() {
		$html = "";

		foreach ($this->fortunes as $fortune) {
			$html .= $fortune->show();
		}

		return $html;
	}
}

class Fortune {
	public $id = null;
	public $time = null;
	public $author = null;
	public $message = null;

	public $posts = array();

	public function __construct($id) {
		$this->id = $id;
	}

	public function load() {
		$fortunes_table = config::get('fortunes')['table'];
		$query  = <<<SQL
		SELECT fortune_id, fortune_time, fortune_login, id, time, login, info, message 
		 FROM {$fortunes_table}
		 WHERE fortune_id = {$this->id}
		 ORDER BY id ASC
SQL;

		$this->posts = config::get('fortunes')['db']->query($query, function($row) {
			$id = $row['id'];

			$tz = new DateTimeZone("Europe/Paris");
			
			$timestr = $row['time'];

			$post_time = new DateTime ($timestr);
			$post_time->setTimeZone ($tz);

			$post_debut = new DateTime($timestr);
			$post_debut->setTimeZone ($tz);
			$post_fin = new DateTime($timestr);
			$post_fin->setTimeZone ($tz);

			$post_debut->modify("-5 minutes");
			$post_fin->modify("+5 minutes");

			$hist_cur = $post_time->format("H:i:s");
			$hist_deb = $post_debut->format("YmdHis");
			$hist_fin = $post_fin->format("YmdHis");
			$hist_jour = $post_fin->format("Y-m-d");

			$info = $row['info'];
			$login = $row['login'];

			$message = $row['message'];

			return array(
				$id => array(
					'id' => $id,
					'info' => $info,
					'login' => $login,
					'message' => $message,
					'time' => $row['time'],
					'hist_deb' => $hist_deb,
					'hist_cur' => $hist_cur,
					'hist_fin' => $hist_fin,
					'hist_jour' => $hist_jour,
				),
			);
		});
	}

	function get_comment() {
		if ($this->message !== null ) {
			if (preg_match('/[^\/]+\/\/(.*)/', $this->message, $matches)) {
				return " « ".trim($matches[1])." »";
			} else
				return null;
		} else {
			return null;
		}
	}


	public function show() {
		$html = <<<EOT
		<div class="fortune">
			<div class="header" id="fortune-{$this->id}">Fortune n° <a class='fortune_no' href='/fortune/{$this->id}'>{$this->id}</a>, par <a class='par' href='/fortunes/par/{$this->author}'>{$this->author}</a> le {$this->time}{$this->get_comment()}</div>

EOT;

		foreach ($this->posts as $post)
			{
			$html .= <<<EOT
		<div class="boardleftmsg">
			[<strong><a href='http://bombefourchette.com/t/dlfp/{$post['hist_jour']}#{$post['id']}' title='id={$post['id']}'>{$post['hist_cur']}</a></strong>]
			<a href='/fortunes/avec/{$post['login']}' title="{$post['info']}">{$post['login']}</a>
		</div>
		<div class="boardrightmsg"><span> <b>-</b> {$post['message']}</span></div>
EOT;
			}

		$html .= <<<EOT
		</div>
EOT;

		return $html;
	}
}

