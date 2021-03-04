# Peji

Peji initialize database, config, view, model
```php

use Peji\DB\DB as DB;
use Peji\Config as Config;
use Peji\View as View;

Config::setDir('../config');
$dbConf = Config::file('db');


define('siteUrl', $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'] );

DB::init( $dbConf['host'], $dbConf['username'], $dbConf['password'], $dbConf['db'] );

DB::setAttr([
	\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'" ,
	\PDO::ATTR_PERSISTENT => false ,
	\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true ,
]);


View::setDir( '../app/View' );

try {
	require_once '../route/route.php';
} catch( Error $e ){
	Peji\Error::manage( $e );
}

```

Peji Routing ...

```php

use Peji\Router as Router;
use Peji\View as View;

function pageNotFount($get = []) {
	return Peji\Bootstrap::start( 'user', 'notfound', 'index', 0, [], $get );
}

Router::setPath( getPath() );

Router::route('admin/{controller?}/{action?}/{id?}', function( $controller = 'index', $action = 'index', $id = 0 ) {
	$params = Router::params();
	$count = count_not_empty( $params );

	if( $count % 2 == 1 ) 
		array_shift( $params );


	if( ! Peji\Bootstrap::start( 'admin', $controller, $action, $id, $params ) ) {
		pageNotFount();
	}
})->where( ['controller' => '[a-zA-Z]+', 'action' => '[^\/]+', 'id' => '[^\/]+'] )->setExtension( [ 'html', 'txt' ] )

->elseRoute( '{:all}', function( $p ) {


	$controller = urldecode($p[0])?:'index';

	$action = urldecode(@$p[1]?:'index');
	$id = urldecode(@$p[2]);
	array_shift( $p );
	array_shift( $p );
	
	$e = explode(".", $controller);
	$controller = $e[0];

	if( ! Peji\Bootstrap::start( 'user', $controller, $action, $id, $p ) ) {
		if( ! Peji\Bootstrap::start( 'user', 'page', 'index', $controller, $p )  ) {
			pageNotFount();
		}
	}

});


Router::dispatch(function( $status ) {

	if( $status == 404 ) {
		pageNotFount();
	}
});

```

Peji Model 

```php
<?php
namespace App\Model;
class Posts extends \Peji\DB\Model {
	var $table = 'posts';
	function getComments() {
		return Comments::sql("where postid = ? ")->find([ $this->id ]);
	}
}

```

```php
use App\Model\Posts;

$posts = Posts::sql("order by id desc")->paginate(10, @$this->get['page']?:1)->find();
foreach( $posts as $post ) {
	print_r( $post->comments );
}

```
