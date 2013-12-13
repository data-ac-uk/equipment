<?php

$f3=require($eq_config->pwd.'/htdocs/lib/base.php');

class equipment 
{
	
	public $config;
	
	function __construct($config){
		$this->config = $config;
	}

	function launch_db(){
		if(!isset($this->db))
			$this->db = new eqDB($this->config->db->connection,$this->config->db->user,$this->config->db->password);
		return $this->db;
	}


	//Loads the config of source from new settings including autodiscovery
	function read_config()
	{
		
		require_once( "{$this->config->pwd}/lib/OPDLib/OrgProfileDocument.php" );
	
	
		$this->launch_db();
	
		$opds = $this->config->opds->direct;
	
		$lopds = scandir($this->config->opds->local);
	
		foreach($lopds as $lopd){
			if($lopd{0}==".") continue;
			$opds[] = array("path"=>$this->config->opds->local."/".$lopd, "type"=>"local");
		}
	
		if(file_exists("{$this->config->pwd}/var/autodiscovered-opds.json")){
			$autoopds = json_decode(file_get_contents("{$this->config->pwd}/var/autodiscovered-opds.json"),true);
			foreach($autoopds as $aopd){
				$opds[] = array("path"=>$aopd, "type"=>"url");
			}
		}
	
	
		//Scans OPDs defined in config;
		foreach($opds as $opd){
		
			$config_line = array();
	
			echo "Loading OPD: {$opd['path']}\n";
	
	
			$topd = @new OrgProfileDocument( $opd['path'] , $opd['type']);

			$graph = $topd->graph;
	
			$primaryTopic = (string)$topd->org;
		
		
		
			$sameas = $graph->resource( $primaryTopic )->all( "http://www.w3.org/2002/07/owl#sameAs" );	
			$uris = array();
			foreach($sameas as $same){
				$uri = parse_url($same);
				$uri['uri'] = (string)$same;
				$uris[$uri['host']] = $uri;
			}

			if(isset($this->config->id->overides[(string)$primaryTopic])){
				$id = explode("/",$this->config->id->overides[(string)$primaryTopic]);
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
			foreach(array('http://purl.org/openorg/theme/equipment','http://purl.org/openorg/theme/facilities') as $gpsk){
				if(isset($graph->t['op'][$gpsk]) && is_array($graph->t['op'][$gpsk])){
					foreach($graph->t['op'][$gpsk] as $gp){
					foreach($gp as $g){
						$durl = (string)$g;
						if(!isset($datas[$durl])) {
							$datas[$durl] = array(
								"conformsTo"=>$graph->resource( $g )->getString('dcterms:conformsTo'),
								"license"=>$graph->resource( $g )->getString('dcterms:license')
							);
}						}
					}
				}
			}
		
			//Loop through found datasets
			foreach($datas as $data => $info){
				//Create a line for the dataset (as there could be more than / organisation)
				$config_gline = $config_line; 
				$config_gline['dataset_url'] = $data;
				$config_gline['dataset_type'] = $this->config->conformsToMap[$info['conformsTo']];
				$config_gline['dataset_corrections'] = $graph->resource( $data )->getString("oo:corrections");
				$config_gline['dataset_contact'] = $graph->resource( $data )->getString("oo:contact");
				$config[$data] = $config_gline;
			}	

		}

		file_put_contents("{$this->config->pwd}/var/opd-config.json", json_encode($config));

		print_r($config);

	}
	
	/**
		Creates and sortable string from txt
		@return string
		@param $txt string
	**/
	function misc_order_txt($txt){
		
		foreach(array('The University of ','University of ','The ') as $k){
			$l = strlen($k);
			if(strcasecmp(substr($txt,0,$l),$k)==0){
				$txt = substr($txt,$l).", ".substr($txt,0,$l);
			}
		}
		return $txt;
	}
	
	/**
		fetches only the header from $url
		@return array
		@param $url string
		@param $follow bool
	**/
	function misc_curl_getinfo($url, $follow = true){
	
		$curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_FILETIME, true);
	    curl_setopt($curl, CURLOPT_NOBODY, true);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if($follow)
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	    $header = curl_exec($curl);
	    $info = curl_getinfo($curl);
		$info['header'] = $header;
		curl_close($curl);
		return $info;
		
	}


	function location_extract($uri, $graph){
		$g=$graph->resource($uri);
				$config_item = array();

		if(($loc = $g->get( "foaf:based_near" ))!="[NULL]"){
			return $this->location_find($loc);
		}
		if(($loc = $g->get( "http://data.ordnancesurvey.co.uk/ontology/postcode/postcode" ))!="[NULL]"){
			return $this->location_find_rdf($loc);
		}
		$sameas = $graph->resource( $uri )->all( "http://www.w3.org/2002/07/owl#sameAs" );	
		$uris = array();
		foreach($sameas as $same){
			$uri = parse_url($same);
			$uri['uri'] = (string)$same;
			$uris[$uri['host']] = $uri;
		}
		if(isset($uris['id.learning-provider.data.ac.uk'])){
			$gid = new graphite();
			$gid->load( $uris['id.learning-provider.data.ac.uk']['uri']);
			if(($loc = $gid->resource($uris['id.learning-provider.data.ac.uk']['uri'])->get( "http://data.ordnancesurvey.co.uk/ontology/postcode/postcode" ))!="[NULL]"){
				return $this->location_find_rdf($loc);
			}
		}
		if(isset($uris['dbpedia.org'])){
			 return $ret = $this->location_find_rdf($uris['dbpedia.org']['uri']);
		}
	}

	function location_find_rdf($loc){
		$g = new graphite();
		$g->load( (string)$loc );
		return $this->location_find($g->resource((string)$loc));
	}
		
	function location_find($loc){
		
		$location = array("loc_uri"=>(string)$loc);
		if( $loc->has( "geo:lat" ) )
		{
			$location["loc_lat"] = $loc->getLiteral( "geo:lat" );
			$location["loc_long"] = $loc->getLiteral( "geo:long" );
		}
		if( $loc->has( "http://data.ordnancesurvey.co.uk/ontology/spatialrelations/easting" ) )
		{
			$location["loc_easting"] = (int)$loc->getLiteral( "http://data.ordnancesurvey.co.uk/ontology/spatialrelations/easting" );
			$location["loc_northing"] = (int)$loc->getLiteral( "http://data.ordnancesurvey.co.uk/ontology/spatialrelations/northing" );
		}
		
		if((isset($location["loc_lat"]) && isset($location["loc_long"])) && ( !isset($location["loc_easting"]) || !isset($location["loc_northing"]) )){
			require_once($this->config->pwd."/lib/phpLocation/phpLocation.php");
			$pos = new phpLocation();
			$pos->lat = $location["loc_lat"];
			$pos->lon = $location["loc_long"];
			$pos->toGrid();
			$location["loc_easting"] = (int)$pos->east;
			$location["loc_northing"] = (int)$pos->north;
		}
		
		if(!isset($location["loc_lat"]) || !isset($location["loc_lat"]) ) return false;
		
		$locationraw = array("loc_updated"=>"NOW()");
		$locationraw['loc_point'] = "POINT({$location["loc_lat"]},{$location["loc_long"]})";
		
		
		$this->launch_db();
		$this->db->insert('locations', $location, $locationraw, 'REPLACE');	
		
		return $location;
	}
}




class eqDB extends DB\SQL {
	
	public $dryrun = false;
	
	function insert($table,$fields,$fieldsraw = array(), $type = 'INSERT'){
		
		
		if($this->dryrun){
			echo "SQL: Insert into: $table\n";
			foreach(array(&$fields,&$fieldsraw) as $a){
				foreach($a as $k=>$v){
					echo "\t{$k}=>".substr($v,0,255)."\n";
				}
			}
			return true;
		}
		
		$i = 1;
		foreach($fields as $k=>$v){
			$fieldsraw[$k] = "?";
			$infields[$i] = $v;
			$i++;
		}
		
		
		
		$sql = "$type into `$table` (`".join("`,`",array_keys($fieldsraw))."`) VALUES (".join(",",array_values($fieldsraw)).");"; 
		$this->exec($sql, $infields);		
		
	}
	
	function where($params, $paramsraw, &$query, &$infields, &$i){
		foreach($params as $k=>$v){
			$query[] = "`$k` = ?";
			$infields[$i] = $v;
			$i++;
		}
		foreach($paramsraw as $k=>$v){
			$query[] = "`$k` = $v";
		}
		
	}
	
	function update($table, $fields,$fieldsraw, $params, $paramsraw = array(), $limit = false){
		
		$i = 1;
		$query = array();
		$infields = array();
		
		if($this->dryrun){
			echo "SQL: Insert into: $table\n";
			foreach(array(&$fields,&$fieldsraw) as $a){
				foreach($a as $k=>$v){
					echo "\t{$k}=>".substr($v,0,255)."\n";
				}
			}
			foreach(array(&$params,&$paramsraw) as $a){
				foreach($a as $k=>$v){
					$query[] = "`$k` = $v";
				}
			}
			echo "\t\tWHERE ".join(" AND ", $query)."\n";
			return true;
		}
		
		
		foreach($fields as $k=>$v){
			$fieldsraw[$k] = "?";
			$infields[$i] = $v;
			$i++;
		}
		
		$fieldsup = array();
		foreach($fieldsraw as $k=>$v){
			$fieldsup[] = "`$k` = $v";
		}
				
		$this->where($params, $paramsraw, $query, $infields, $i);
	
		$sql = "UPDATE {$table} SET ".join(", ",$fieldsup)." WHERE ".join(" AND ", $query);
		
		if($limit)
			$sql .= " Limit ".$limit;

		return $this->exec($sql, $infields);
	}
	
	
	function fetch_many($table, $params = array(), $paramsraw = array(), $what = "*", $limit = false){
		$i = 1;
		$query = array();
		$infields = array();
		
		$this->where($params, $paramsraw, $query, $infields, $i);
		
		$sql = "SELECT {$what} FROM {$table} WHERE ".join(" AND ", $query);
		
		if($limit)
			$sql .= " Limit ".$limit;

		return $this->exec($sql, $infields);
	}
	
	function fetch_one($table, $params = array(), $paramsraw = array(), $what = "*"){
		$res = $this->fetch_many($table, $params, $paramsraw, $what, 1);
		if(!isset($res[0]))
			return false;
		else
			return $res[0];
	}
}