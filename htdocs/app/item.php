<?php
class item {

	function page() {
                $f3=Base::instance();
		$id = $f3->get( "PARAMS.id" );

		$id = preg_replace( '/[^a-f0-9]/','',$id );
		readfile( "../var/item/$id" );
	}
}
