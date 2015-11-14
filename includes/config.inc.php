<?php

require __DIR__.'/../config/config.inc.php';

class config {
	static private $config;
	static private $id;

	static public function init($tribune = NULL) {
		global $global_config;

		self::$config = $global_config['dlfp.moul.es'];
		self::$id = 'dlfp.moul.es';
		
		global $argv;
		if (isset($_GET['host'])) {
			$host = $_GET['host'];
		} else if (isset($argv[1])) {
			$host = $argv[1];
		} else {
			list($host) = explode('.', $_SERVER['HTTP_HOST']);
		}

		if (isset($global_config[$host])) {
			self::$config = $global_config[$host];
			self::$id = $host;
		}

		if (isset($tribune) and isset($global_config[$tribune . ".moul.es"])) {
			self::$config = $global_config[$tribune . ".moul.es"];
			self::$id = $host;
		}
	}

	static public function id() {
		return self::$id;
	}

	static public function get($key) {
		if (!empty(self::$config[$key])) {
			return self::$config[$key];
		} else {
			return null;
		}
	}
}

class debug {
	static public function dump() {
		$args = func_get_args();

		$dump = '';

		foreach ($args as $arg) {
			if (is_int($arg) and $arg > 1000000000 and $arg < 2000000000) {
				$dump .= date("[d/m/Y]", $arg)." ".self::dump_as_string($arg);
			} else {
				$dump .= self::dump_as_string($arg);
			}
		}

		$dump = self::get_backtrace_as_string() . $dump."\n";

		if (php_sapi_name() != 'cli') {
			$dump = '<pre style="text-align:left;padding:3px;margin:3px;border:solid 1px #000;color:#000;background-color:#eee">' . $dump . '</pre>';
		}

		echo $dump;

	}

	public static function dump_as_string($var) {
		ob_start();
		var_dump($var);
		$dump = ob_get_contents();
		ob_end_clean();
		return $dump;
	}

	public static function get_backtrace_as_string() {
		$string = '';
		$level = 0;

		foreach(array_reverse(array_slice(debug_backtrace(), 1)) as $backtrace) {
			if (!isset($backtrace['file'])) {
				$backtrace['file'] = 'unknown';
			}

			if (!isset($backtrace['line'])) {
				$backtrace['line'] = 'unknown';
			}

			$string .= str_repeat('  ', $level++).'=> ' . ($backtrace['file']) . ' on line ' . $backtrace['line'] . "\n";
		}

		return $string;
	}

}

