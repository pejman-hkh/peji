<?php
namespace Peji;

class Error extends Singleton {

	protected function manage( $e ) {

		if( preg_match('#Class \'App\\\Model\\\(.*?)\' not found#is', $e->getMessage(), $m ) ) {
			 $mf = '../app/Model/'.$m[1].'.php';
			if( ! file_exists( $mf )) {

				file_put_contents( $mf, '<?php
namespace App\Model;
class '.$m[1].' extends \Peji\DB\Model {
	var $table = \''.strtolower( $m[1]).'\';
}');
				echo "We created it for you ! Please reload page.<br />";
				chmod( $mf, 0777);
			}

		}

		echo $e->getMessage();	
		echo "<br />";	
		echo 'In file '.$e->getFile();		
		echo "<br />";	
		echo 'In line '.$e->getLine();		
	}
}


?>