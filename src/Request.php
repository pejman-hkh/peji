<?php
namespace Peji;

class Request extends Singleton {
	public function filterGet( $get ) {
		$arr = array();
		foreach( $get as $k => $v ) {
			$arr[ $k ] = is_array( $v ) ? $this->filterGet( $v ) : htmlspecialchars( $v, ENT_QUOTES, 'UTF-8' );
		}
		
		return $arr;
	}

	protected function get( $key = '' ) {
		return $key ? htmlspecialchars($_GET[$key], ENT_QUOTES, 'UTF-8') : $this->filterGet($_GET);
	}

	protected function post( $key = '' ) {
		return $key ? $_POST[$key] : $_POST;
	}

	protected function server( $key = '' ) {
		return $key ? $_SERVER[$key] : $_SERVER;
	}

	protected function all() {
		$a = new \StdClass();
		$a->post = $this->post();
		$a->get = $this->get();
		$a->server = $this->server();
		return $a;

	}

}

?>