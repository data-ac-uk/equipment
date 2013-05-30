<?php
class item {

	function fragment() {
                $f3=Base::instance();
		$id = $f3->get( "PARAMS.id" );
		$id = preg_replace( '/[^a-f0-9]/','',$id );

		$data = json_decode( file_get_contents( "../var/item/$id" ), true );
		print "<h2>".$data["title"]."</h2>";

		print $data["content"];
	}

	function page()
	{
                $f3=Base::instance();
		$id = $f3->get( "PARAMS.id" );
		$id = preg_replace( '/[^a-f0-9]/','',$id );

		$data = json_decode( file_get_contents( "../var/item/$id" ), true );

		$f3->set('html_title', $data["title"] );
		$f3->set('content','content.html');
		$f3->set('html_content', $data["content"] );
		print Template::instance()->render( "page-template.html" );
	}
}
