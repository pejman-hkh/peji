<?php
namespace Peji;

class Config extends Singleton {

	protected function setDir( $dir ) {
		$this->dir = $dir;
	}

	protected function file( $file ) {
		$this->data[ $file ] = include( $this->dir.'/'.$file.'.php' );
		return $this->data[ $file ];
	}

	protected function get( $key ) {
		return $this->data[ $key ];
	}
}

?>