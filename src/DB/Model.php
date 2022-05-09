<?php
namespace Peji\DB;
use Peji\Request;

class Model {
	var $columns, $columnsType;
	function __construct() {

		if( @count( $this->columns ) == 0 ) {

		}
	}

	public static $instance;
	public static function getInstance( $class ) {

		if( @$ret = self::$instance[ $class ] ) {
			return $ret;
		}

		self::$instance[$class] = new $class();
		return self::$instance[$class];
	}

	function makeColumns( $columns ) {
	
		$class = get_called_class();
		$o = self::getInstance( $class );

		$columns = array_reverse( $columns );
		$this->getColumns();

		if( count( $this->columns ) > 1 ) {
			return;
		}

		foreach( $columns as $v ) {
			if( $v == 'ajax' ) continue;

			if( in_array( $v , $this->columns ) )  continue;
			$type = 'VARCHAR(255)';
			if( $v == 'note' || $v == 'text' || $v == 'desc' || $v == 'shortdesc' ) {
				$type = 'TEXT';
			}

			if( $v == 'date' || $v == 'mobile' || $v == 'number' || $v == 'price' || preg_match('#id#', $v ) ) {
				$type = 'INT(11)';
			}

			DB::sql("ALTER TABLE `$o->table` ADD `$v` $type NOT NULL AFTER `id`")->execute();
		}


		if( ! in_array('date', $columns ) ) {
			DB::sql("ALTER TABLE `$o->table` ADD `date` INT(11) NOT NULL AFTER `id`")->execute();
		}

		$this->columns = [];
		$this->getColumns( false );
	}

	function setObj( $obj ) {
		foreach( $obj as $k => $v ) {
			$this->columns[] = $k;
			$this->$k = $v;
		}
	}

	function setIt( $obj ) {
		$this->makeColumns( array_keys($obj) );
		foreach( $obj as $k => $v ) {
			$this->$k = $v;
		}
	}

	public function __set($name,$value) {
		$a = 'set'.$name;
		if( method_exists($this, $a ) ) {
			return $this->$a( $name, $value );
		}

		$this->$name = $value;
	}

	var $cacheGet;

	public function __get($name) {
		$a = 'get'.$name;
		if( @$this->cacheGet[$a] ) {
			return $this->cacheGet[$a];
		}

		if( method_exists($this, $a ) ) {
			$this->cacheGet[$a] = $ret = $this->$a();
			return $ret;
		}

		return @$this->$name;
	}

	public static function field( $fields = '' ) {
	
		$class = get_called_class();
		$a = DHC::getInstance( $class );
	
		$o = self::getInstance( $class );

		$sql = "select $fields from $o->table ".(@$sql?:"");
		
		return $a->sql( $sql, @$o->table, '' );
	}

	public static function where( $arr = '' ) {
	
		$class = get_called_class();
		$a = DHC::getInstance( $class );
	
		$o = self::getInstance( $class );

		$sql = "where 1 = 1 ";
		$bind = [];
		foreach( $arr as $k => $v ) {
			$bind[] = $v;
			$sql .= " and $k = ? ";
		}

		$sql = "select * from $o->table ".($sql?:"");
		
		return $a->sql( $sql, @$o->table, '', $bind );
	}

	public static function sql( $sql = '', $bind = [] ) {
		$msql = $sql;
		$class = get_called_class();
		$a = DHC::getInstance( $class );
	
		$o = self::getInstance( $class );
		if( ! preg_match('#^\s*select#is', $sql ) ) {

			$sql = "select * from $o->table ".($sql?:"");
		}

		return $a->sql( $sql, @$o->table, $msql, $bind );
	}

	public static function find( $arr = [] ) {
		$class = get_called_class();
		$o = self::getInstance( $class );
		$a = DHC::getInstance( $class );

		if( is_array( $arr ) ) {
			return $a->sql( "select * from $o->table ".@$arr[0] )->find( @$arr['bind'] );
		} else {
			return @$a->sql( "select * from $o->table where id = ? " )->find( [@$arr] )[0];

		}
	}

	public static function getPaginate() {
		$class = get_called_class();

		return DHC::getInstance( $class )->getPaginate();
	}

	public static function findFirst( $arr = [] ) {
		$ret = self::find($arr);
		return $ret[0];
	}

	public function getColumns( $cache = true ) {
		$db = DB::$db;
		if( \Peji\Cache::get('columns_'.$this->table) && $cache ) {
			$this->columns = \Peji\Cache::get('columns_'.$this->table);
			$this->columnsType = \Peji\Cache::get('columnsType_'.$this->table);
			return;
		}

		if( ! $cache ) {
			$this->columns = [];
			$this->columnsType = [];
		}

		$columns = $db->prepare("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$this->table."' and table_schema = '".DB::$name."'  ")->execute()->findAll();
		foreach( $columns as $v ) {
			$this->columns[] = $v['COLUMN_NAME'];
			$this->columnsType[] = [ $v['COLUMN_NAME'],  $v['DATA_TYPE'] ];
		}

		\Peji\Cache::set('columns_'.$this->table, $this->columns );
		\Peji\Cache::set('columnsType_'.$this->table, $this->columnsType );
	}

	public function delete() {
		$sql = "DELETE FROM ".$this->table." where id = ? ";
		$db = DB::$db;

		return $db->prepare( $sql )->execute( [ $this->id ] );
	}

	public function save() {
		$db = DB::$db;


		if( ! @$this->recordExists ) {
			$this->getColumns();
		}

		if( count( $this->columns ) == 0 ) {
			$this->columns[] = 'test';
		}

		if( count( $this->columns ) == 1 ) {
			$pass = (array)$this;
			unset( $pass['table'] );
			unset( $pass['columns'] );
			unset( $pass['columnsType'] );
			$this->setIt( $pass );
		}

		$vals = [];
		foreach( $this->columns as $v ) {
			$type = gettype( $this->$v );
			if( is_array( $this->$v ) ) {
				$this->$v = implode(",", $this->$v);
			}
			
			if( $type == "integer" ) {
				$vals[] = (int)$this->$v;
			} else if( $type == "string" ) {
				$vals[] = (string)$this->$v;
			} else if( $type == "double" ) {
				$vals[] = (double)$this->$v;			
			} else {
				$vals[] = (string)$this->$v;
			}
		}

		if( @$this->recordExists ) {
			$vals[] = $this->id;
			$sql = "UPDATE `".$this->table."` SET ".'`'.implode('` = ?, `', $this->columns ).'` = ? '." WHERE id = ? ";

		} else {
			$sql = "INSERT INTO `".$this->table."`(".'`'.implode("` , `", @$this->columns ).'`'.") VALUES(".( str_repeat('?,', count( @$this->columns ) - 1 ).'?' ).")";
		}
		
		$db->prepare( $sql )->execute( $vals );
		if( ! $this->recordExists ) {
			$this->id = $db->lastInsertId();
		}

		$this->recordExists = 1;
		return $this->id;
	}
}
