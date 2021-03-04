<?php
namespace Peji;

class Controller extends Singleton {
	public $set;
	public function setKey( $key, $val ) {
		$this->$key = $val;
	}

	public function set( $key, $val = [] ) {
		if( is_array( $key ) ) {
			foreach( $key as $k => $v ) {
				$this->set[ $k ] = $v;
			}
		} else {
			$this->set[ $key ] = $val;
		}
	}

	public function get() {
		return $this->set;
	}
}