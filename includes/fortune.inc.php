<?php

class Fortunes {
	public $author = null;
	public $actor = null;
	public $id = null;

	public $limit = 40;

	public $fortunes = array();

	public function select() {
		$query = "SELECT DISTINCT fortune_id, fortune_time, fortune_post_id, fortune_info, fortune_login, fortune_message FROM ".config::get('fortunes')['table']." WHERE 1";
		if (isset($this->author))
			{
			if (strpos($this->author, "info:") === 0)
				{
				$info = substr($this->author, 5);
				$query .= " AND fortune_info='".config::get('fortunes')['db']->real_escape_string($info)."'";
				}
			else
				{
				$query .= " AND fortune_login='".config::get('fortunes')['db']->real_escape_string($this->author)."'";
				}
			}
		if (isset($this->actor))
			{
			if (strpos($this->actor, "info:") === 0)
				{
				$info = substr($this->actor, 5);
				$query .= " AND info='".config::get('fortunes')['db']->real_escape_string($info)."'";
				}
			else
				{
				$query .= " AND login='".config::get('fortunes')['db']->real_escape_string($this->actor)."'";
				}
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
			$fortune->author = $fortune->post->display_username();
			$fortune->author_login = $fortune->post->login;
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

	public $post = null;

	public $posts = array();

	public function __construct($id) {
		$this->id = $id;
	}

	public function load() {
		$fortunes_table = config::get('fortunes')['table'];
		$query  = <<<SQL
		SELECT *
		 FROM {$fortunes_table}
		 WHERE fortune_id = {$this->id}
		 ORDER BY id ASC
SQL;

		 $fortune = $this;

		$this->posts = config::get('fortunes')['db']->query($query, function($row) use($fortune) {
			if (!isset($fortune->post)) {
				$fortune->post = new Post([
					'id' => $row['fortune_post_id'],
					'time' => $row['fortune_time'],
					'info' => $row['fortune_info'],
					'login' => $row['fortune_login'],
					'message' => $row['fortune_message'],
				]);
			}

			return array($row['id'] => new Post($row));
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
		if ($this->post->is_anonymous()) {
			$link_fortunes_by = "/fortunes/par/info:{$this->post->info}";
		} else {
			$link_fortunes_by = "/fortunes/par/{$this->post->login}";
		}

		$html = <<<EOT
		<div class="fortune">
			<div class="header" id="fortune-{$this->id}">Fortune n° <a class='fortune_no' href='/fortune/{$this->id}'>{$this->id}</a>, par <a class='par' href='{$link_fortunes_by}'>{$this->author}</a> le {$this->time}{$this->get_comment()}</div>

EOT;

		$history_base = config::get('history')['url'];

		foreach ($this->posts as $post)
			{
			if ($history_base) {
				$history_url = strtr($history_base, [
					'%post_day' => $post->datetime()->format('Y-m-d'),
					'%post_id' => $post->id,
				]);

				$clock = "<a href='{$history_url}' title='id={$post->id}'>{$post->clock()}</a>";
			} else {
				$clock = $post->clock();
			}

			if ($post->is_anonymous()) {
				$link_fortunes_with = "/fortunes/avec/info:{$post->info}";
			} else {
				$link_fortunes_with = "/fortunes/avec/{$post->login}";
			}

			$html .= <<<EOT
	<div class="post">
		<span class="post-clock">{$clock}</span>
		<span class="post-author"><a href='$link_fortunes_with' title="{$post->info}">{$post->display_username()}</a></span>
		<span class="message">{$post->message}</span>
	</div>
EOT;
			}

		$html .= <<<EOT
		</div>
EOT;

		return $html;
	}
}

