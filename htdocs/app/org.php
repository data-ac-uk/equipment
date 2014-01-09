<?php
class org {

	function page()
	{
		$f3=Base::instance();
				
		$idtype = $f3->get( "PARAMS.type" );
		$idtype = preg_replace( '/[^-a-z0-9]/i','',$idtype );
		$id = $f3->get( "PARAMS.id" );
		$datset = $f3->get( "PARAMS.dataset" );
		$suffix = pathinfo($datset, PATHINFO_EXTENSION);
		$datset = preg_replace( '/[^-a-z0-9]/i','', pathinfo($datset, PATHINFO_FILENAME) );

		$contenttypes = array("json"=>"application/json","csv"=>"text/csv", "tsv"=>"text/tsv","ttl"=>"text/turtle");

		switch($suffix){
		
			case "json":
			case "csv":
			case "tsv":
			case "ttl":
				if(!file_exists("data/org/$idtype-$id-$datset.$suffix")){
					$f3->error(404);
				}
				header('Content-Type: '.$contenttypes[$suffix]);
				print file_get_contents( "data/org/$idtype-$id-$datset.$suffix" );
			break;
			case "":
			case "html":
				if(!file_exists("data/org/$idtype-$id-$datset.json")){
					$f3->error(404);
				}
				$data = json_decode( file_get_contents( "data/org/$idtype-$id-$datset.json" ), true );
				$fields = json_decode( file_get_contents( "../var/uniquip-fields.json" ), true );
				$eq = $f3->eq;
				$metadata = $eq->get_dataset($datset, 'data_hash', array('crawl','org'));
				$f3->set('metadata', $metadata );
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


