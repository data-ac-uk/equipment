<?php
class org {

	function page()
	{
                $f3=Base::instance();
		$id = $f3->get( "PARAMS.id" );
		$id = preg_replace( '/[^-a-z0-9]/i','',$id );

		$data = json_decode( file_get_contents( "data/org/$id.json" ), true );
		$fields = json_decode( file_get_contents( "../var/uniquip-fields.json" ), true );

		$f3->set('metadata', $data["metadata"] );
		$f3->set('records', $data["records"] );
		$f3->set('fields', $fields );
		$f3->set('html_title', "Equipment from ".$data["metadata"]["org_name"] );
		$f3->set('content','org.html');
		print Template::instance()->render( "page-template.html" );
	}
}

function markup( $text )
{
	$html = htmlspecialchars( $text );
	$html = preg_replace("/[[:alpha:]]+:\/\/[^<>[:space:]]+[[:alnum:]\/]/","<a href=\"\\0\">\\0</a>", $html );
	return $html;
}


