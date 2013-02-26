<?php
class status {

	function page() {
                $f3=Base::instance();

		$f3->set('html_title', "Status Report" );
		$f3->set('content','content.html');
		$c = array();
		$c []= "HELLO";
		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	}
}
