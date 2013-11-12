<?php
class org {

	function page()
	{
        $f3=Base::instance();
				
		$idtype = $f3->get( "PARAMS.type" );
		$idtype = preg_replace( '/[^-a-z0-9]/i','',$idtype );
		$id = $f3->get( "PARAMS.id" );
		$suffix = pathinfo($id, PATHINFO_EXTENSION);
		$id = preg_replace( '/[^-a-z0-9]/i','', pathinfo($id, PATHINFO_FILENAME) );

		$contenttypes = array("json"=>"application/json","csv"=>"text/csv", "tsv"=>"text/tsv","ttl"=>"text/turtle");

		switch($suffix){
		
				case "json":
				case "csv":
				case "tsv":
				case "ttl":
				if(!file_exists("data/org/$idtype-$id.$suffix")){
					$f3->error(404);
				}
				header('Content-Type: '.$contenttypes[$suffix]);
				print file_get_contents( "data/org/$idtype-$id.$suffix" );
			break;
			case "":
			case ".html":
				if(!file_exists("data/org/$idtype-$id.json")){
					$f3->error(404);
				}
		
				$data = json_decode( file_get_contents( "data/org/$idtype-$id.json" ), true );
				$fields = json_decode( file_get_contents( "../var/uniquip-fields.json" ), true );

				$f3->set('metadata', $data["metadata"] );
				$f3->set('records', $data["records"] );
				$f3->set('fields', $fields );
				$f3->set('html_title', "Equipment from ".$data["metadata"]["org_name"] );
				$f3->set('content','org.html');
				print Template::instance()->render( "page-template.html" );
			break;
			default:
				$f3->error(404);
			break;
		}
	}
}

function markup( $text )
{
	$html = htmlspecialchars( $text );
	$html = preg_replace("/[[:alpha:]]+:\/\/[^<>[:space:]]+[[:alnum:]\/]/","<a href=\"\\0\">\\0</a>", $html );
	return $html;
}


