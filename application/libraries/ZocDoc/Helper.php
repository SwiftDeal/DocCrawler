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
		$file = self::path().'codes.php';
		require_once($file);
		return $zip_codes;
	}

	public static function last() {
		$file = self::path(). 'last.txt';
		$content = file_get_contents($file);
		
		if ($content) {
			return $content;
		} else {
			return false;
		}
	}
}
