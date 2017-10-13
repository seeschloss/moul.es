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

	function test($message, $runs = 100) {
		$descriptorspec = array(
		   0 => array("pipe", "r"),
		   1 => array("pipe", "w"),
		   2 => array("pipe", "w"),
		);

		$variants = [];

		foreach ($this->slips as $i => $slip) {
			if (!isset($this->processes[$i])) {
			  // stdbuf est là pour forcer les slips à flusher chaque ligne qui sort, sinon
			  // comme stdout/stderr sont des pipes et pas un TTY, il y a un gros buffer (lol)
			  // et mon fgets() bloque comme un con parce qu'il n'a rien à lire
			  $p = proc_open("stdbuf -oL -eL ".$slip->cmd, $descriptorspec, $pipes, $slip->cwd, [
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

			$time_start = microtime(true);
			for ($run = 0; $run < $runs; $run++) {
			  $written = fwrite($pipes[0], $message."\n");
			  fflush($pipes[0]);
			  $result = fgets($pipes[1]);
			}
			$time_delta = microtime(true) - $time_start;
			$time_delta /= $runs;

			$variants[$i] = [
				'slip' => $this->slips[$i],
				'output' => trim($result, "\n"),
				'time' => $time_delta,
			];
		}

		return $variants;
    }

	function result($message, $runs = 100) {
		$html = "";

		if (isset($_REQUEST['fast'])) {
			$runs = 1;
		}

		$variants = $this->test($message, $runs);
		$escaped_message = htmlspecialchars($message);

		$total = count($variants);
		$unique = count(array_unique(array_map(function($d) { return $d['output']; }, $variants)));

		$test_id = md5($message);

		$html .= <<<HTML
			<a href="#{$test_id}" id="{$test_id}">#</a>
			<table class="test" data-unique="{$unique}">
				<tr>
					<th>slip</th>
					<th>langage</th>
					<th>temps</th>
					<th>résultat</th>
					<th>visuel</th>
				</tr>
				<tr>
					<td colspan="3"><em>message original</em></td>
					<td colspan="2">{$escaped_message}</td>
				</tr>
HTML;

		foreach ($variants as $slip_id => $variant) {
			$variants[$slip_id]['amount'] = count(array_filter($variants, function($a) use($variant) { return $a['output'] === $variant['output']; }));
		}

		usort($variants, function($a, $b) {
			$amount = $b['amount'] - $a['amount'];

			if ($amount != 0) {
				return $amount;
			}

			$output = strcmp($a['output'], $b['output']);

			if ($output != 0) {
				return $output;
			}

			return ($a['time'] * 1000 * 1000) - ($b['time'] * 1000 * 1000);
		});

		foreach ($variants as $slip_id => $variant) {
			$escaped_result = htmlspecialchars($variant['output']);

			$visuel = $this->client_slip($variant['output'], 'encoded');

			$slip = $variant['slip'];

			$time = str_pad(round($variant['time'] * 1000, 3), 5, '0');
			$time_ms = $variant['time'] * 1000;
			$percent = round($variant['amount']/$total, 2);

			$html .= <<<HTML
				<tr class="slip" data-amount="{$variant['amount']}" data-percent="{$percent}" data-time="{$time_ms}" data-lang="{$slip->lang}">
					<td><nobr><a href="{$slip->link()}">{$slip->name}</a></nobr></td>
					<td class="lang">{$slip->lang}</td>
					<td class="time">{$time}ms</td>
					<td class="result">{$escaped_result}</td>
					<td class="visuel">{$visuel}</td>
				</tr>
HTML;
		}

		$html .= "</table>";

		return $html;
	}

	function results() {
		echo <<<HTML
			<style>
				table.test {
					margin-bottom: 2em;
				}

			td.result {
				white-space: pre;
			}
			</style>
HTML;

		if ($this->post) {
			echo $this->result($this->post->message);
		} else {
			echo $this->result("temps de lancement", 1);

			foreach ($this->test_messages() as $message) {
				echo $this->result($message);
			}
		}

		$this->cleanup();

		echo <<<HTML
			<script src="https://d3js.org/d3.v4.min.js"></script>
			<script>
				let tests = document.querySelectorAll('table.test');

				let lang_scale = d3.scaleOrdinal(d3.schemeCategory10)

				for (var i = 0; i < tests.length; i++) {
					let test = tests.item(i);
					console.log(test);

					let slips = test.querySelectorAll('tr.slip');

					let performance_scale = d3.scaleLinear()
						.domain(d3.extent(Array.prototype.map.call(slips, slip => +slip.dataset.time)))
						.range(['turquoise', 'red']);

					let result_scale = d3.scaleLinear()
						.domain([0, 1])
						.range(['white', 'lime']);

					slips.forEach(slip => {
						slip.querySelector('td.lang').style.color             = lang_scale(slip.dataset.lang);
						slip.querySelector('td.time').style.backgroundColor   = performance_scale(+slip.dataset.time);
						slip.querySelector('td.result').style.backgroundColor = result_scale(+slip.dataset.percent);
					});
				}
			</script>
HTML;
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

	function client_slip($message, $backend_type = 'raw') {
	  $text = htmlspecialchars($message, ENT_NOQUOTES);

	  $dom = new DOMDocument;
	  @$dom->loadXML('<message>' . $text . '</message>');

	  $post = $dom->firstChild;

	  if ($post) {
		foreach ($post->childNodes as $node) {
		  if (isset($node->tagName)) switch ($node->tagName) {
			case 'a':
			case 'b':
			case 'i':
			case 'u':
			case 's':
			  break;
			default:
			  $node->parentNode->replaceChild($dom->createTextNode($node->textContent), $node);
			  break;
		  }
		}

		$post = $dom->saveHTML($post);
		$post = str_replace('<message>', '', $post);
		$post = str_replace('</message>', '', $post);
		if ($backend_type != 'raw') {
		  $post = html_entity_decode($post);
		}
		return $post;
	  } else {
		// Let's stay safe, but still try to display something.
		return "<em>(XML invalide)</em>";
	  }
	}
}

