<?php
namespace Peji;

class Router extends Singleton {

	var $params, $wa, $uri, $callback;
	var $whered = false;

	public function where( $a, $b = '' ) {
		$this->whered = true;

		if( is_array( $a ) ) {
			$arr = [];
			foreach( $a as $k => $v ) {
				$arr[ $k ] = $v;
			}

			$this->wa[] = $arr;
		} else {
			$this->wa[] = array(  $a => $b );
		}

		return $this;
	}

	public function setExtension( $ext ) {
		$this->extension = true;

		if( is_array( $ext ) ) {
			$this->ea[] = $ext;
		} else {
			$this->ea[] = array( $ext );
		}

		return $this;
	}

	protected function setPath( $path ) {
		$this->path = $path;
	}

	private $getExten;

	public function checkUrl( $uri, $wa, $extension = array() ) {

		$pathArr = $this->pathArr = explode( "/", $this->path );
		
		$last = $pathArr [ count( $pathArr ) - 1 ];
		$expLast = explode(".", $last);

		$expUri = explode( '/', $uri );

		$extension1 = [];
		if( @count( $extension ) > 0 ) foreach( $extension as $k => $v ) {
			if( is_array( $v ) ) {
				if( $pathArr[0] === $k ) {
					foreach( $v as $k1 => $v1 ) {
						$extension1[] = $v1;
					}
				}
			} else {
				$extension1 [] = $v;
			}
		}

		unset( $extension );

		if( count( $expLast ) > 1 && in_array( $expLast[1] , (array)$extension1 )  ) {
			$pathArr [ count( $pathArr ) - 1 ] = $expLast[0];
		}
			
		$this->getExten = isset( $expLast[1] ) ? $expLast[1] : '';

		$params = array();
		foreach( $expUri as $k => $v ) {

			$v1 = $v;

			$v2 = isset( $pathArr[ $k ] ) ? $pathArr[ $k ] : '';

			if( $v1 !== @$v2 ) {

				if( substr( $v1, 0, 1 ) === '{' && substr( $v1, -1 ) === '}' ) {

					$p = str_replace( array('{', '}', '?', ':'), '', $v1 );

					$checkRgx = true;

					if( substr( $v1, 1, 1 ) === ':' ) {
						if( $p === 'all' ) {
							$params[] = $pathArr;
							break;
						}
					}

					if( substr( $v1, -2, -1 ) !== '?' ) {

						if( empty( $v2 ) ) {
							return false;
						}

					} else {

						if( empty( $v2 ) ) {
							$checkRgx = false;
						}
					}

					if( $checkRgx && isset( $wa[ $p ] ) && ! preg_match( '#^('.$wa[ $p ].')$#iu', $v2 ) ) {
						return false;
					}

					if( isset( $v2 ) && ! empty( $v2 )  ) {
						$params[] = $v2;
					}
					
				} else {
					return false;
				}
			}

		}

		$this->params = $params;

		return true;
	}

	protected function params() {
		return $this->pathArr;
	}

	protected function route( $uri, $callback ) {
		$this->uri[] = $uri;
		$this->callback[] = $callback;

		return $this;
	}

	private $extension;

	public function elseRoute( $uri, $callback ) {
		
		if( ! $this->whered ) {
			$this->wa[] = [];
		}
		
		if( ! $this->extension ) {
			$this->ea[] = [];
		}

		$this->whered = false;
		$this->extension = false;

		$this->route( $uri, $callback );
	
		return $this;
	}

	private function run( $uri, $callback, $wa, $extension ) {

		if( $this->checkUrl( $uri, $wa, $extension ) ) {

			if( is_callable( $callback ) ) {
				$ret = call_user_func_array( $callback, $this->params );
			} else {
				$ret = $callback;
			}
		
			return true;
		}

		return false;

	}

	protected function dispatch( $callback ) {

		foreach( $this->uri as $k => $v ) {
			if( $this->run( $v, $this->callback[ $k ], @$this->wa[ $k ], @$this->ea[ $k ] ) ) {
				$callback(200);
				return;
			}
		}
		$callback(404);
	}
}

