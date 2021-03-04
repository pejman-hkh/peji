<?php
namespace Peji;

class Singleton {

	private static $Classes;

	public static function __callStatic($method, $args) {
		$className = get_called_class();

    	return call_user_func_array( array( self::$Classes[ $className ] = @self::$Classes[ $className ]?:( class_exists( $className ) ? new $className() : false ), $method ), $args );
    }


	protected function getInstance() {
		$className = get_called_class();
		return @self::$Classes[ $className ];
	}

}