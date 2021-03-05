<?php
namespace Peji\DB;

class DB {
	public static $db;
	public static $name;

	public static function init( $host, $user, $pass, $dbn ) {
		$db = new pdoWrapper();
		$db->connect($host, $user, $pass, $dbn );
		self::$name = $dbn;
		
		self::$db = $db;
	}

	public static function sql( $sql = "" ) {
		$a = new DHC( new Model );
		return $a->sql( $sql );
	}

	public static function setAttr( $arr ) {
		return self::$db->setAttr( $arr );
	}
}