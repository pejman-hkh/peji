<?php
namespace Peji;

class Cookie extends Singleton {

	protected function get( $key = '' ) {
		return $key?@$_COOKIE[ $key ]:$_COOKIE;
	}

	protected function set( $key, $val, $day = 365, $path = '/', $domain = '', $secure = false, $httponly = true ) {
		$domain = $domain?:App::domain();

		setcookie($key,$val, time() + ($day * 3600 * 24), $path, $domain, $secure, $httponly);
	}

	protected function unset( $key ) {
		unset( $_COOKIE[ $key ] );
		$this->set( $key, null, -1 );
	}	

}


?>