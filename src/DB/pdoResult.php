<?php
namespace Peji\DB;

class pdoResult  {
	private $statment, $wrapper, $sql;

	public function __construct( $statment, $wrapper ) {
		$this->statment = $statment ;
		$this->wrapper = $wrapper;
		$this->sql = $wrapper->getSql();

	}
	
	public function next() {
		return $this->statment->fetch( \PDO::FETCH_ASSOC  );
	}

	public function fetch() {
		return $this->next();
	}

	public function findAll() {
		return $this->statment->fetchAll( \PDO::FETCH_ASSOC );		
	}

	public function find( $callback ) {

		$std = new StdClass();
		while( $fetch = $this->next() ) {
			$std->item = $fetch;

			foreach ($fetch as $key => $value) {
				$std->$key = ( $value );
			}

			$callback( $std, $this->wrapper );
		}
	}

	public function numRows() {
		return $this->statment->rowCount();
	}

	public function count() {
		return $this->numRows();
	}

}
