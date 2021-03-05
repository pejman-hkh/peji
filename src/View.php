<?php
namespace Peji;

class View extends Singleton {
	private $set = [];
	private $dir;
	
	protected function setDir( $dir ) {
		$this->mainDir = $dir;
	}

	private function resetAll() {
		$this->mainDir = '';
		$this->set = [];
	}

	protected function render( $layout ) {
		if( @count( $this->set ) > 0 ) foreach( $this->set as $k => $v ) {
			$this->$k = $v;
		}

		extract( $this->set );

		$path = $this->mainDir.'/'.$layout.'.html';
		if(  file_exists( $path ) )
			include( $path );

		$this->resetAll();
	}

	protected function set( $set, $v = '' ) {
		if( $v == '')
			$this->set = $set;
		else
			$this->set[$set] = $v;
	}

	protected function get() {
		return $this->set;
	}

	protected function fetch( $adir ) {
		extract( $this->set );
		include( $this->mainDir.'/'.$this->set['dir'].'/'.$adir.'.html' );
	}
}