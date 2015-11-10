<?php
namespace ZocDoc;

class Helper {
	private function __construct() {
		// do nothing
	}

	private function __clone() {
		// do nothing
	}

	public static function path() {
		$file = dirname(__FILE__). '/data/';
		return $file;
	}

	public static function zip_codes() {
		$file = self::path().'codes.php';
		include($file);
		return $zip_codes;
	}
}
