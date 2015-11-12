<?php
namespace ZocDoc;

class Helper {
	private function __construct() {
		// do nothing
	}

	private function __clone() {
		// do nothing
	}

	protected static function path() {
		$file = dirname(__FILE__). '/data/';
		return $file;
	}

	public static function zip_codes() {
		$file = self::path().'major-codes.php';
		require_once($file);
		return $zip_codes;
	}

	public static function lastRunCode($code = "") {
		$file = self::path(). 'last.txt';
		if ($code) {
			file_put_contents($file, $code);
		} else {
			$content = file_get_contents($file);
			return ($content) ? $content : false;	
		}
	}

}
