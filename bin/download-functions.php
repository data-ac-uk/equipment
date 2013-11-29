<?php


//Loads the config of source from new settings including autodiscovery
function read_config_new( )
{
	
	global $eq_config;
	
	
	
	require_once( "{$eq_config->pwd}/lib/opd/OrgProfileDocument.php" );
	
	$opds = $eq_config->opds->direct;
	
	
	$lopds = scandir($eq_config->opds->local);
	
	foreach($lopds as $lopd){
		if($lopd{0}==".") continue;
		$opds[] = array("path"=>$eq_config->opds->local."/".$lopd, "type"=>"local");
	}
	
	if(file_exists("{$eq_config->pwd}/var/autodiscovered-opds.json")){
		$autoopds = json_decode(file_get_contents("{$eq_config->pwd}/var/autodiscovered-opds.json"),true);
		foreach($autoopds as $aopd){
			$opds[] = array("path"=>$aopd, "type"=>"url");
		}
	}
	
	
	//Scans OPDs defined in config;
	foreach($opds as $opd){
		
		$config_line = array();
	
		echo "Loading OPD: {$opd['path']}\n";
	
	
		$topd = new OrgProfileDocument( $opd['path'] , $opd['type']);

		$graph = $topd->graph;
	
		$primaryTopic = (string)$topd->org;
		
		
		
		$sameas = $graph->resource( $primaryTopic )->all( "http://www.w3.org/2002/07/owl#sameAs" );	
		$uris = array();
		foreach($sameas as $same){
			$uri = parse_url($same);
			$uri['uri'] = (string)$same;
			$uris[$uri['host']] = $uri;
		}

		if(isset($eq_config->id->overides[(string)$primaryTopic])){
			$id = explode("/",$eq_config->id->overides[(string)$primaryTopic]);
			$config_line['org_idscheme'] = $id[0];
			$config_line['org_ukprn'] = NULL;
			$config_line['org_id'] = $id[1];
		}elseif(isset($uris['id.learning-provider.data.ac.uk'])){
			$config_line['org_idscheme'] = 'ukprn';
			$path = explode("/",$uris['id.learning-provider.data.ac.uk']['path']);
			$config_line['org_ukprn'] = $path[2];
			$config_line['org_id'] = $path[2];
		}else{
			$config_line['org_idscheme'] = 'other';
			$config_line['org_ukprn'] = NULL;
			$config_line['org_id'] = 'X?';
		}
		
		$config_line['org_name'] = $graph->resource( $primaryTopic )->getString('foaf:name');
		if(!strlen($config_line['org_name']))
			$config_line['org_name'] = $graph->resource( $primaryTopic )->getString('skos:prefLabel');
			
		$config_line['org_url'] = $graph->resource( $primaryTopic )->getString('foaf:homepage');
		$config_line['org_logo'] = $graph->resource( $primaryTopic )->getString('foaf:logo');

		
		$datas = array();
		foreach(array($graph->t['op']['http://purl.org/openorg/theme/equipment'],
			$graph->t['op']['http://purl.org/openorg/theme/facilities']) as $gps){
			if(is_array($gps))
			foreach($gps as $gp){
				foreach($gp as $g){
					$durl = (string)$g;
					if(!isset($datas[$durl])) {
						$datas[$durl] = array(
							"conformsTo"=>$graph->resource( $g )->getString('dcterms:conformsTo'),
							"license"=>$graph->resource( $g )->getString('dcterms:license')
						);
					}
				}
			}
		}
		
		//Loop through found datasets
		foreach($datas as $data => $info){
			//Create a line for the dataset (as there could be more than / organisation)
			$config_gline = $config_line; 
			$config_gline['dataset_url'] = $data;
			$config_gline['dataset_type'] = $eq_config->conformsToMap[$info['conformsTo']];
			$config_gline['dataset_corrections'] = $graph->resource( $data )->getString("oo:corrections");
			$config_gline['dataset_contact'] = $graph->resource( $data )->getString("oo:contact");
			$config[$data] = $config_gline;
		}	

	}

	file_put_contents("{$eq_config->pwd}/var/opd-config.json", json_encode($config));

	print_r($config);

}
