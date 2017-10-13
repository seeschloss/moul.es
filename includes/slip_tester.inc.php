<?php

class Slip {
	public $persistent = true;

	public function source() {
		return file_get_contents($this->code);
	}

	public function link() {
		return "/slip/" . str_replace("Slip_", "", get_class($this));
	}
}

class Slip_Slipounet extends Slip {
	public $cmd = 'unshare -n '.__DIR__.'/../bin/slip-slipounet.php';
	public $code = __DIR__.'/../bin/slip-slipounet.php';
	public $name = 'Slipounet (PHP)';
}

class Slip_SlipounetNG extends Slip {
	public $cmd = 'unshare -n '.__DIR__.'/../bin/slip-slipounet-ng.php';
	public $code = __DIR__.'/../bin/slip-slipounet-ng.php';
	public $name = 'Slipounet NG (PHP)';
}

class Slip_D7 extends Slip {
	public $cmd = 'unshare -n '.__DIR__.'/../bin/slip-d7.php';
	public $code = __DIR__.'/../bin/slip-d7.php';
	public $name = 'D7 (PHP)';
}

class Slip_Miaoli extends Slip {
	public $cmd = 'unshare -n node '.__DIR__.'/../bin/slip-miaoli.js';
	public $code = __DIR__.'/../bin/slip-miaoli.js';
	public $name = 'Miaoli (JS)';
}

class Slip_GoBoard extends Slip {
	public $cmd = 'unshare -n '.__DIR__.'/../bin/slipcleaner';
	public $code = __DIR__.'/../bin/slipcleaner.go';
	public $name = 'GoBoard (Go)';
}

class Slip_JSlip extends Slip {
	public $cmd = 'unshare -n java -jar '.__DIR__.'/../bin/jslip-1.0.1.jar';
	public $code = __DIR__.'/../bin/jslip-1.0.1.java';
	public $name = 'JSlip (Java)';
}

class Slip_Sveetch extends Slip {
	public $cmd = 'unshare -n python2 '.__DIR__.'/../bin/slip-sveetch.py';
	public $code = __DIR__.'/../bin/slip-sveetch.py';
	public $name = 'Django tribune (Python 2)';
}

class Slip_cnitize extends Slip {
	public $cmd = 'unshare -n /home/seeschloss/src/cnitize/cnitize';
	public $code = '/home/seeschloss/src/cnitize/cnitize.c';
	public $name = 'cnitize (C)';
}

class Slip_DLFP extends Slip {
	public $cmd = 'ruby --encoding utf-8 '.__DIR__.'/../bin/slip-dlfp.rb';
	public $code = __DIR__.'/../bin/slip-dlfp.rb';
	public $name = 'DLFP (Ruby)';
}

class Slip_Tester {
	public $post = null;

	public $slips = [];

	private $processes = [];

	function __construct($post_id = null) {
		if ($post_id) {
			$this->post = Post::get($post_id, null, true);
		}

		$this->slips = [
			new Slip_Slipounet(),
			new Slip_SlipounetNG(),
			new Slip_D7(),
			new Slip_Miaoli(),
			new Slip_GoBoard(),
			new Slip_JSlip(),
			new Slip_Sveetch(),
			new Slip_cnitize(),
			new Slip_DLFP(),
		];
	}

    function test_messages() {
      return [
        'message de base',
        '<i>tags <b>imbriqués</b> correctement</i>',
        '<i>tags <b>qui</i> se</b> chevauchent',
        'tag ouvert <i>mais pas fermé',
		'<i>italique</i> <b>gras</b> <u>souligné</u> <s>barré</s>',
		'<m>Moment</m>',
		'<tt>teletype</tt>',
		'<code>avec des <i>balises</i></code>',
		'tags <b></b> inutiles',
		'tags avec des attributs <b class="test">en trop</b>',
		'signes < et > littéraux, et avec des <b>tags<</b>',
		'signes & " et \' littéraux',
		'lien dans une <a href="http://example.com">balise</a>.',
		'entités HTML : &gt; &lt; &quot; &amp; &nbsp;',
		"caractères de contrôle : \x08 (backspace) \x1B (escape)",
		"unicode à la con : 🐧 (manchot, 4 octets) 👩‍👩‍👧‍👦 (deux femmes, une fille, et un garçon, en 7 glyphes et 25 octets)",
		'Horloges : 14:00 14:00:00 14:00:00²',
		'Totoz : [:totoz] [:velasquez:5]',
        'URL : http://example.com',
        'URL avec des espaces : http://example.com/chemin%20avec%20des%20espaces?param=des+espaces',
        'URL (dans une parenthèse http://example.com)',
        'URL suivie d\'une virgule http://example.com, comme ça',
        'URL suivie d\'un totoz http://example.com[:totoz]',
        'URL avec un numéro de port http://example.com:80/chemin',
      ];
    }

	function test($message) {
		$descriptorspec = array(
		   0 => array("pipe", "r"),
		   1 => array("pipe", "w"),
		   2 => array("pipe", "w"),
		);

		$variants = [];

		foreach ($this->slips as $i => $slip) {
			if (!isset($this->processes[$i])) {
			  $p = proc_open("stdbuf -oL -eL ".$slip->cmd, $descriptorspec, $pipes, "/tmp", [
				  'HOME' => '/home/seeschloss',
				  'LC_ALL' => 'en_US.UTF-8',
			  ]);

			  $this->processes[$i] = [
				  'proc' => $p,
				  'pipes' => $pipes,
			  ];
			}
			$p = $this->processes[$i]['proc'];
			$pipes = $this->processes[$i]['pipes'];

			$runs = 100;
			if (!$slip->persistent) {
				$runs = 1;
			}

			$time_start = microtime(true);
			for ($run = 0; $run < $runs; $run++) {
			  $written = fwrite($pipes[0], $message."\n");
			  fflush($pipes[0]);
			  if (!$slip->persistent) {
				  fclose($pipes[0]);
			  }

			  //usleep(1000);

			  $result = fgets($pipes[1]);

			  if (!$slip->persistent and !$result) {
				  $result = stream_get_contents($pipes[2]);
			  }
			}
			$time_delta = microtime(true) - $time_start;
			$time_delta /= $runs;

			if (!$slip->persistent) {
				foreach ($this->processes[$i]['pipes'] as $pipe) {
					@fclose($pipe);
				}
				proc_close($this->processes[$i]['proc']);

				unset($this->processes[$i]);
			}

			$variants[$i] = [
				'slip' => $this->slips[$i],
				'output' => trim($result, "\n"),
				'time' => $time_delta,
			];
		}

		return $variants;
    }

	function result($message) {
		$html = "";

		$variants = $this->test($message);
		$escaped_message = htmlspecialchars($message);

		$html .= <<<HTML
			<table>
				<tr>
					<th>slip</th>
					<th>temps</th>
					<th>résultat</th>
				</tr>
				<tr>
					<td><em>original</em></td>
					<td></td>
					<td>{$escaped_message}</td>
				</tr>
HTML;
		$unique = array_unique(array_map(function($d) { return $d['output']; }, $variants));
		if (count($unique) > 1) {
			foreach ($variants as $slip_id => $variant) {
				$escaped_result = htmlspecialchars($variant['output']);

				$slip = $variant['slip'];

				$time = str_pad(round($variant['time'] * 1000, 3), 5, '0');

				$html .= <<<HTML
					<tr>
						<td><nobr><a href="{$slip->link()}">{$slip->name}</a></nobr></td>
						<td>{$time}ms</td>
						<td>{$escaped_result}</td>
					</tr>
HTML;
			}
		} else {
			$escaped_result = htmlspecialchars($variants[0]['output']);

			$html .= <<<HTML
				<tr>
					<td><nobr>Unanimité</nobr></td>
					<td></td>
					<td>{$escaped_result}</td>
				</tr>
HTML;
		}

		$html .= "</table>";

		return $html;
	}

	function results() {
		if ($this->post) {
			echo $this->result($this->post->message);
		} else {
			foreach ($this->test_messages() as $message) {
				echo $this->result($message);
			}
		}

		$this->cleanup();
	}

	function cleanup() {
		foreach ($this->processes as $i => $proc) {
			foreach ($proc['pipes'] as $pipe) {
				fclose($pipe);
			}
			proc_close($proc['proc']);
		}

		$this->processes = [];
	}
}

