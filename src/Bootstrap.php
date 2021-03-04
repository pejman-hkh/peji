<?php
namespace Peji;

class Bootstrap extends Singleton {
	
	protected function start( $dir, $controller, $action, $id, $params = [], $get = [] ) {

		$nController = "App\Controller\\".ucfirst($dir)."\\{$controller}Controller";

		$get['dir'] = $dir;
		$get['controller'] = $controller;
		$get['action'] = $action;
		$get['id'] = $id;
		$get['baseUrl'] = baseUrl;
		$get['get'] = Request::get();
		$get['post'] = Request::post();
		$get['server'] = Request::server();
	
		if( class_exists( $nController ) ) {
			$nControllerObject = new $nController();
			if( @$nControllerObject->toIndex ) {

				$oid = $id;
				$oaction = $action;
				$id = $action; 
				$action = 'index';
				$get['action'] = 'index';
				$get['id'] = $id = $oaction == 'index' ? $oid : $id ;
	
			}
			foreach( $get as $k => $v ) {
				$nControllerObject->setKey( $k, $v );
			}
		} else {
			return false;
		}

		App::call( $nControllerObject, 'beforeApp' ) ;
		App::call( $nControllerObject, 'before' ) ;

		if( App::call( $nControllerObject, $action, [ $id, $params ] ) ) {
	
			$get += (array)$nControllerObject->get();

			View::set( $get );
			if( ! @$nControllerObject->disableView ) {

				View::render( $dir.'/base/'.(isset($nControllerObject->layout)?$nControllerObject->layout:'index') );
			}

			App::call( $nControllerObject, 'after' ) ;
			App::call( $nControllerObject, 'afterApp' ) ;
			return true;
		}


		return false;
	}
}

?>