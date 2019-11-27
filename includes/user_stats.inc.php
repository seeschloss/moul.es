<?php

class User_Stats {
	public $login;

	private function query($query, $function, $db = null) {
		if ($db === null) {
			$db = config::get('history')['db'];
		}

		return $db->query($query, $function);
	}

	function __construct($login = null) {
		$this->login = $login;
	}

	function escaped_login() {
		return str_replace("'", "\\'", $this->login);
	}

	function daily_activity() {
		if (!isset($this->total_posts)) {
			$query = "
				SELECT
				  COUNT(*) as nb,
				  WEEKDAY(STR_TO_DATE(time, '%Y%m%d%H%i%s')) as week_day
				FROM ".config::get('history')['table']."
				WHERE login='".$this->escaped_login()."'
				GROUP BY WEEKDAY(STR_TO_DATE(time, '%Y%m%d%H%i%s'))
			";

			$posts_per_day = array();
			$this->query($query, function($row) use(&$posts_per_day) {
				$posts_per_day[$row['week_day']] = $row['nb'];
			});
		}

		return debug::dump($posts_per_day);
	}

	static function hourly_activity_gif($logins) {
		$cache_dir = __DIR__.'/../cache/'.config::id().'/stats.gif';
		
		if (!file_exists($cache_dir)) {
			mkdir($cache_dir, 0777, TRUE);
		}

		$cache_key = strtr(implode(",", $logins), array(
			" " => "",
			"/" => "",
			"." => "",
		)).".gif";

		if (file_exists($cache_dir."/".$cache_key)) {
			if (time() - filectime($cache_dir."/".$cache_key) > 3600*24) {
			//if (time() - filectime($cache_dir."/".$cache_key) > 3600*24 || filesize($cache_dir."/".$cache_key) < 100) {
				unlink($cache_dir."/".$cache_key);
			}
		}

		if (!file_exists($cache_dir."/".$cache_key)) {
			$activity = array();
			$first_post = 0;
			$last_post = 0;
			foreach ($logins as $login) {
				$user_stats = new User_Stats($login);

				if (!$first_post) {
					$first_post = $user_stats->first_post();
				} else {
					$first_post = min($first_post, $user_stats->first_post());
				}

				if (!$last_post) {
					$last_post = $user_stats->last_post();
				} else {
					$last_post = min($last_post, $user_stats->last_post());
				}
			}

			$max = 0;
			for ($time = $last_post->datetime(); $time >= $first_post->datetime(); $time->modify("-1 day")) {
				$start = clone $time;
				$start->modify("-1 week");
				$stop = $time;

				foreach ($logins as $login) {
					$data = $user_stats->hourly_activity($login, $start, $stop);
					$max = max($max, max($data));

					$activity[$start->getTimestamp()][$login] = $data;
				}
			}

			$frames = array();
			foreach ($activity as $day => $login_activity) {
				$title = date("d/m/Y", $day);
				$frames[] = $user_stats->hourly_activity_chart($login_activity, $title, NULL, $max);
			}

			$gif = new Imagick();
			$gif->setFormat('GIF');

			$frames = array_reverse($frames);
			foreach ($frames as $frame) {
				$gif_frame = new Imagick();
				$gif_frame->readImageBlob($frame);
				$gif->addImage($gif_frame);
				$gif->setImageDelay(20);
				$gif->nextImage();
			}
			$gif->setImageDelay(200);

			$gif->writeImages($cache_dir."/".$cache_key, true);
		}

		return file_get_contents($cache_dir."/".$cache_key);
	}

	function hourly_activity_chart($activity, $title = NULL, $min = NULL, $max = NULL) {
		include_once __DIR__.'/../libraries/pChart/class/pData.class.php';
		include_once __DIR__.'/../libraries/pChart/class/pDraw.class.php';
		include_once __DIR__.'/../libraries/pChart/class/pImage.class.php';

		$data_max = 0;

		/* Create and populate the pData object */
		$data = new pData();
		foreach ($activity as $login => $posts_per_hour) {
			$data->addPoints(array_values($posts_per_hour), $login);
			$data_max = max($data_max, max($posts_per_hour));
		}
		if (empty($activity)) {
			$data->addPoints(array(), "");
		}
		$data->setAxisName(0, "Posts");
		$data->setAxisPosition(0, AXIS_POSITION_LEFT);
		$data->addPoints(range(0, 23), "Heures");
		$data->setSerieDescription("Heures", "Heure");
		$data->setAbscissa("Heures");
		$data->setXAxisUnit("h");

		/* Create the pChart object */
		$image = new pImage(800, 450, $data);

		/* Turn of Antialiasing */
		$image->Antialias = FALSE;

		/* Set the default font */
		$image->setFontProperties(array(
			"FontName" => __DIR__."/../libraries/pChart/fonts/verdana.ttf",
			"FontSize" => 10,
		));

		/* Define the chart area */
		$image->setGraphArea(60, 40, 750, 400);

		/* Draw the scale */
		$scaleSettings = array(
			"GridR" => 200,
			"GridG" => 200,
			"GridB" => 200,
			"DrawSubTicks" => TRUE,
			"CycleBackground" => TRUE,
			"Mode" => SCALE_MODE_MANUAL,
			"ManualScale" => array(
				0 => array(
					"Min" => $min === NULL ? 0 : $min,
					"Max" => $max === NULL ? $data_max * 1.1 : $max,
				),
			),
		);
		$image->drawScale($scaleSettings);

		/* Write the chart legend */
		$image->drawLegend(180, 12, array(
			"Style" => LEGEND_NOBORDER,
			"Mode" => LEGEND_HORIZONTAL,
		));

		if ($title) {
			$image->drawText(200, 450, $title, array(
				"FontSize" => 20,
				"Align" => TEXT_ALIGN_BOTTOMRIGHT,
			));
		}

		/* Turn on shadow computing */ 
		$settings = array();
		$image->drawBarChart($settings);

		/* Render the picture */
		ob_flush();
		ob_start();
		imagepng($image->Picture);
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

	function hourly_activity($login, $from = NULL, $to = NULL) {
		if ($login != "*") {
			$query = "
				SELECT
				  COUNT(*) as nb,
				  HOUR(STR_TO_DATE(time, '%Y%m%d%H%i%s')) as hour
				FROM ".config::get('history')['table']."
				WHERE login='".str_replace("'", "\\'", $login)."'
			";

			if ($from and $to) {
				$from_string = $from->format("YmdHis");
				$to_string = $to->format("YmdHis");

				$query .= "
					AND time BETWEEN '".$from_string."' AND '".$to_string."'
				";
			}

			$query .= "
				GROUP BY HOUR(STR_TO_DATE(time, '%Y%m%d%H%i%s'))
			";

			$posts_per_hour = array();
			foreach (range(0, 23) as $hour) {
				$posts_per_hour[$hour] = "0";
			}

			$this->query($query, function($row) use(&$posts_per_hour) {
				$posts_per_hour[$row['hour']] = $row['nb'];
			});
		} else {
			$query = "
				SELECT
				  COUNT(*) as nb,
				  HOUR(STR_TO_DATE(time, '%Y%m%d%H%i%s')) as hour
				FROM ".config::get('history')['table'];

			if ($from and $to) {
				$from_string = $from->format("YmdHis");
				$to_string = $to->format("YmdHis");

				$query .= "
					WHERE time BETWEEN '".$from_string."' AND '".$to_string."'
				";
			}

			$query .= "
				GROUP BY HOUR(STR_TO_DATE(time, '%Y%m%d%H%i%s'))
			";

			$posts_per_hour = array();
			foreach (range(0, 23) as $hour) {
				$posts_per_hour[$hour] = "0";
			}

			$this->query($query, function($row) use(&$posts_per_hour) {
				$posts_per_hour[$row['hour']] = $row['nb'];
			});
		}

		return $posts_per_hour;
	}

	function first_post() {
		$query = "
			SELECT *
			FROM ".config::get('history')['table'];
		if ($this->login != "*") {
			$query .= "
				WHERE login='".str_replace("'", "\\'", $this->login)."'
			";
		}
		$query .= "
			ORDER BY time ASC
			LIMIT 1
		";

		$post = array();

		$this->query($query, function($row) use(&$post) {
			$post = $row;
		});

		return Post::get($post['id'], $post['time']);
	}

	function last_post() {
		$query = "
			SELECT *
			FROM ".config::get('history')['table'];
		if ($this->login != "*") {
			$query .= "
				WHERE login='".str_replace("'", "\\'", $this->login)."'
			";
		}
		$query .= "
			ORDER BY time DESC
			LIMIT 1
		";

		$post = array();

		$this->query($query, function($row) use(&$post) {
			$post = $row;
		});

		return Post::get($post['id'], $post['time']);
	}
}

