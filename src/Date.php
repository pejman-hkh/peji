<?php
namespace Peji;
class Date extends Singleton {
	function show( $a, $t ) {
		if( Lang::get() == 'en' ) {
			return date( $a, $t);	
		}
	}
}
?>