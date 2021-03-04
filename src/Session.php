<?php
namespace Peji;

class Session extends Singleton {

	protected function get( $key = '' ) {
		return $key?@$_SESSION[$key]:$_SESSION;
	}

	protected function set( $key, $val ) {
		@session_start();
		$_SESSION[ $key ] = $val;
	}

	protected function unset( $key ) {
		@session_start();
		unset( $_SESSION[ $key ] );
	}
}


?>