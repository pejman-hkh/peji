<?php
namespace Peji;

class Cache extends Singleton {
	var $data;

	protected function get( $key = '' ) {
		return $key?@$this->data[ $key ]:$this->data;
	}

	protected function set( $key, $val ) {
		$this->data[ $key ] = $val;
	}

	protected function unset( $key ) {
		unset( $this->data[ $key ] );

	}	

}


?>