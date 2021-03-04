<?php
namespace Peji;

class App extends Singleton {
	protected function domain() {
		return Request::server('HTTP_HOST');
	}

	protected function key() {
		$keyFile = '../cache/key.php';
		if( ! file_exists( $keyFile ) ) {
			$key =  md5(rand(0,9999999));

			file_put_contents( $keyFile, '<?php return "'.$key.'"?>' );

			return $key;
		}

		return include( $keyFile );
	}

	function call( $class, $method, $args = [] ) {
		if( method_exists( $class, $method ) ) {
			call_user_func_array( [$class, $method], $args );
			return true;
		}
		return false;
	}

}


?>