<?php
namespace Peji\DB;

class DHC {

	function __construct( $class ) {
		$this->db = DB::$db;
		$this->class = $class;
	}

	public static $instance;
	public static function getInstance( $class ) {

		if( @$ret = self::$instance[ $class ] ) {
			return $ret;
		}

		self::$instance[ $class ] = new self( $class );
		return self::$instance[ $class ];
	}

	function sql( $sql = '', $table = '', $extraSql = '' ) {
		$this->sql = $sql;
		$this->table = $table;
		$this->extraSql = $extraSql;

		return $this;
	}

	function execute( $bind = [] ) {
		return $this->db->prepare( $this->sql )->execute(@$bind);
	}

	function count( $bind = [] ) {

		$this->sql = " select count(*) as count from ".$this->table." ".$this->extraSql;

		$fetch = $this->db->prepare( $this->sql )->execute(@$bind)->fetch();

		return $fetch['count'];
	}

	function find( $bind = [] ) {
		$class = $this->class;
		//$o = new $class();


		if( @count( $this->paginateData ) > 0 ) {
			if( strtolower( substr( $this->sql, 0, 6 ) ) == 'select' ) {
				$this->sql = substr( $this->sql, 6);
				$this->sql = 'SELECT SQL_CALC_FOUND_ROWS '.$this->sql;
			}
		}


		$query = $this->db->prepare( $this->sql )->execute(@$bind);

		if( @$this->paginateData ) {
			$fetch = $this->db->prepare("SELECT FOUND_ROWS()")->execute()->fetch();
			$this->count = @$fetch["FOUND_ROWS()"];
		}

		$ret = [];
		while( $v = $query->next() ) {
			$o = new $class();
			$o->recordExists = true;
			$o->setObj( $v );
	
			$ret[] = $o;
		}

		return $ret;
	}

	function findFirst( $bind = [] ) {
		return @$this->find( $bind )[0];
	}

	public function paginate( $limit, $page = 1 ) {
		$this->paginateData = [ $limit, $page ];
		$c = (int)( $page * $limit - $limit );
		$limit = (int)$limit;
		$this->sql .= " limit ".($c > 0 ? $c : 0).", $limit";

		return $this;
	}

	public function getPaginate() {

		$number = @$this->paginateData[0]?:1;
		$page = $this->paginateData[1];

		unset($this->paginateData);

		$count = $this->count;

		$limit = 4;
		$nP = ceil( $count / $number );

		$data["start"] = ( $page - $limit ) <= 0 ? 1 : $page - $limit;
		$data["end"] = ( $page + $limit >= $nP ) ? $nP : $page + $limit;
		$data["count"] = $count;
		$data["endPage"] = ceil($count / $number);
		$data["next"] = $page >= ceil( $count / $number ) ? $page : $page + 1;
		$data["prev"] = $page <= 1 ? 1 : $page - 1;

		return $data;	
	}	
}
