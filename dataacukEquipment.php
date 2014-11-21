<?php

if(!isset($f3))
	$f3=require($eq_config->pwd.'/htdocs/lib/base.php');

error_reporting(0);

class dataacukEquipment 
{
	
	public $config;
	public $cache;
	
	function __construct($config){
		$this->config = $config;
		$this->cache = (object) NULL;
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
	
	
	function proc_opd(&$opd, $src){
		$ins = array();
		$insf = array();
	
		$ins['opd_id'] = (string)$opd->org;
		$ins['opd_url'] = (string)$opd->opd_url;
		$ins['opd_ena'] = 1;
		$insf['opd_lastseen'] = 'NOW()';
		$ins['opd_type'] = (string)$opd->result['CONTENT_TYPE'];
		$ins['opd_cache'] = (string)$opd->result['CONTENT'];
		$ins['opd_src'] = $src;
		

		$res = $this->db->fetch_one('autoOPDs', array('opd_id' => $ins['opd_id']), array(), "`opd_id`");
		if(isset($res['opd_id']))
			$this->db->update('autoOPDs', $ins, $insf, array('opd_id' => $ins['opd_id']));
		else{
			$insf['opd_firstseen'] = 'NOW()';
			$this->db->insert('autoOPDs', $ins, $insf);
		}
	
	}
	
	
	
	
	/**
		Returns org 
		@return array
		@param $org_uri string
	**/
	
	function get_org($org_uri){
		$this->launch_db();
		return $this->db->fetch_one('orgs', array('org_uri' => $org_uri));
	}
	
	function get_dataset($org_uri, $key = 'data_uri', $include = array()){
		$this->launch_db();
		$ret = $this->db->fetch_one('datasets', array($key => $org_uri));
		if(in_array('org',$include)){
			$ret['org'] = $this->get_org($ret['data_org']);
		}
		if(in_array('crawl',$include)){
			$ret['crawl'] = $this->get_crawl($ret['data_crawl']);
		}
		return $ret;
	}
	
	function get_location($loc_uri){
		$this->launch_db();
		return $this->db->fetch_one('locations', array('loc_uri' => $loc_uri));
	}
	
	function get_crawl($crawl_id){
		$this->launch_db();
		return $this->db->fetch_one('crawls', array('crawl_id' => $crawl_id));
	}
	
	
	function parse_finish($set, $notes){
		$crawl = array();
		$crawl['crawl_dataset'] = $set['data_uri'];
		if(count($notes['errors'])){
			$crawl['crawl_success'] = 'error';
		}elseif(count($notes['errors'])){
			$crawl['crawl_success'] = 'warning';
		}else{
			$crawl['crawl_success'] = 'ok';
		}
		$ret = $this->db->exec('SELECT count(*) as count FROM  `items` WHERE  `item_dataset` LIKE  ?', $set['data_uri']);
		$crawl['crawl_records'] = $ret[0]['count'];
		$crawl['crawl_notes'] = json_encode($notes);
		if(!$crawl['crawl_success']!='error'){
			$gong = $this->parse_gong($set);
			$crawl['crawl_gong'] = $this->config->gongs[min($gong)];
			$crawl['crawl_gong_json'] = json_encode($gong);
		}
		
		$crawl_id = $this->db->insert('crawls', $crawl, array("crawl_timestamp"=>"NOW()"));

		$this->db->update('datasets',  array('data_crawl'=>$crawl_id), array(), array('data_uri' => $set['data_uri']));
		$set['data_crawl'] = $crawl_id;
		
		
		if($crawl['crawl_success'] == 'error' && strtolower(substr($set['data_corrections'],0,7))=="mailto:"){
			$rest = $this->db->fetch_many('crawls', array('crawl_dataset'=>$set['data_uri'], "sort:"=>"d:crawl_timestamp"), array(), "`crawl_success`", "1,1");
			if(isset($rest[0]['crawl_success']) and $rest[0]['crawl_success']=='error'){
				echo "Sending Message - \n";
				$crawlnotes = array();
				foreach($notes as $k=>$notes){
					if(count($notes)==0) continue;
					$crawlnotes[] = ucwords($k).":";
					foreach($notes as $note){
						$crawlnotes[] = "  * {$note}";
					}
				
				}
				$fields = array();
				$fields['error_text'] = join("\n",$crawlnotes);
				$fields['datset_url'] = $set['data_uri'];
				$this->messageFromTemplate("equipment-download-error", "andrew@bluerhinos.co.uk", $fields, 'alert', $set['data_uri']);
				}
		}
		
	}
	
	function parse_gong(&$set){
		//Auto OPD
		$gongs = array();
		$gongs['data'] = 3;
		switch($set['data_src']){
			case "autodiscovered":
				$gongs['opd'] = 3;
				$gongs['opd-auto'] = 3;
			break;
			case "hosted":
				$gongs['opd'] = 3;
				$gongs['opd-auto'] = 2;
			break;
			default:
				$gongs['opd'] = 1;
				$gongs['opd-auto'] = 1;
			break;
		}
		
		//Find license if not best can do is silver;
		$lifound = false;
		foreach($this->config->licences as $li){
			if($li['uri']==$set['data_license']){
				$lifound = true;
				break;
			}
		}
		
		if($lifound){
			$gongs['licence'] = 3;
		}else{
			$gongs['licence'] = 2;
		}
		
		return $gongs;
		
	}
	
	
	function parse_pure($set,$path, &$notes)
	{
		$xml = simplexml_load_string( file_get_contents( $path ) );

		$graph = new eqGraphite();
		$graph->ns( "org", "http://www.w3.org/ns/org#" );
		$graph->ns( "gr", "http://purl.org/goodrelations/v1#" );
		$graph->ns( "oldcerif", "http://spi-fm.uca.es/neologism/cerif#" );
		foreach( $xml->equipment as $item )
		{
			$id = md5((string)$item->uid);
			$uri = "http://id.equipment.data.ac.uk/item/$id";
			$url = "http://equipment.data.ac.uk/item/$id.html";

			$graph->addCompressedTriple( $uri, "rdf:type", "oo:Equipment" );

			$graph->addCompressedTriple( "$uri", "http://id.equipment.data.ac.uk/ns/hasCode", $id, "literal" );
			$graph->addCompressedTriple( "$uri", "http://id.equipment.data.ac.uk/ns/hasURI", "$uri", "literal" );
			$graph->addCompressedTriple( "$uri", "http://id.equipment.data.ac.uk/ns/hasPage", "$url" );

			$graph->addCompressedTriple( $uri, "rdfs:label", (string)$item->title, "literal" );
			if($set['org']['org_idscheme']=='ukprn'){
				$graph->addCompressedTriple( $uri, "oo:formalOrganization", "http://id.learning-provider.data.ac.uk/ukprn/{$set['org']['org_id']}" );
			}
			if( $item->description != "" )
			{
				# kitcat always makes HTML fragment descriptions	
				$graph->addCompressedTriple( $uri, "dcterms:description", (string)$item->description, "http://purl.org/xtypes/Fragment-HTML" );
			}


			if( $item->owner != "" )
			{
				$org_id = "";
				foreach( $item->owner->attributes() as $k=>$v )
				{
					if( $k == "shortName" ) { $org_id = $v; }
				}
				if( !isset( $org_id ) )
				{ 
					$org_id = md5( (string)$item->owner  ); 
				}
				$org_uri = "http://id.equipment.data.ac.uk/org/{$c['org_idscheme']}/{$c["org_id"]}/org/".rawurlencode($org_id);
				$graph->addCompressedTriple( $uri, "oo:organizationPart", $org_uri );
				$graph->addCompressedTriple( $org_uri, "rdfs:label", (string)$item->owner, "literal" );
				$graph->addCompressedTriple( $org_uri, "rdf:type", "http://www.w3.org/ns/org#Organization" );
				if($c["org_idscheme"]=='ukprn'){
					$graph->addCompressedTriple( "http://id.learning-provider.data.ac.uk/ukprn/".$c["org_ukprn"], "org:hasSubOrganization", $org_uri );
				}
			}
			
			
			if( $item->phone != "" || $item->email != "" )
			{	
				$graph->addCompressedTriple( $uri, "oo:contact", "$uri#contact1" );
				$graph->addCompressedTriple( $uri, "oo:primaryContact", "$uri#contact1" );
				if( $item->email != "" )
				{
					if(strcasecmp(substr($item->email,0,7),"mailto:")!=0){
						$item->email = "mailto:".$item->email;
					}
					$graph->addCompressedTriple( "$uri#contact1", "foaf:mbox", (string)$item->email );
				}
				if( $item->phone != "" )
				{
					$graph->addPhone( "$uri#contact1", $item->phone );
				}
			}


			if( $item->website != "" )
			{	
				$graph->addCompressedTriple( $uri, "foaf:page", $item->website );
			}
		}

		return $graph;
	}
	
	/**
		Parses rdf file
		@return bool
		@param $set array
		@param $path string
	**/
	function parse_rdf($set,$path, &$notes){
		
		$graph = new eqGraphite();
		$graph->ns( "oldcerif", "http://spi-fm.uca.es/neologism/cerif#" );

		$tmpfile_err = "{$this->config->cachepath}/{$set['data_hash']}.err";
		$tmpfile_nt = "{$this->config->cachepath}/{$set['data_hash']}.nt";
		
		exec( "{$this->config->rapper->path} -g ".escapeshellarg($path)." -q > $tmpfile_nt 2> $tmpfile_err" );
		
		$errors = file_get_contents( $tmpfile_err );
		unlink( $tmpfile_err );

		if( $errors != "" )
		{
			#unlink( $tmpfile_nt );
			$notes["errors"][] = "Parse error: ".$errors;
			return false; 
		}
		
		$n = $graph->load( $tmpfile_nt );
		unlink( $tmpfile_nt );

		if( $n==0 ) 
		{ 
			$notes["errors"][] = "No triples loaded";
			return false; 
		}
		
		$aliases = array();
		foreach( array( "oo:Facility", "oldcerif:Facility", "oo:Equipment", "oldcerif:Equipment" ) as $type )
		{
			foreach( $graph->allOfType( $type ) as $item  )
			{
				$id = md5( "$item" );
				$uri = "http://id.equipment.data.ac.uk/item/$id";
				$url = "http://equipment.data.ac.uk/item/$id.html";
				$graph->addCompressedTriple( "$item", "http://id.equipment.data.ac.uk/ns/hasCode", $id, "literal" );
				$graph->addCompressedTriple( "$item", "http://id.equipment.data.ac.uk/ns/hasURI", "$uri", "literal" );
				$graph->addCompressedTriple( "$item", "http://id.equipment.data.ac.uk/ns/hasPage", "$url" );
				$aliases[ "$item" ] = $uri;	
			}
		}
		
		return  $graph->cloneGraphWithAliases( $aliases );
	}
	
	
	function parse_kitcat($set,$path, &$notes){
		$content = file_get_contents( $path );
		$items = json_decode( $content, true );

		$graph = new eqGraphite();
		foreach( $items as $item )
		{
			$their_uri = $item["id"];


			$id = md5( $their_uri );
			$our_uri = "http://id.equipment.data.ac.uk/item/$id";
			$url = "http://equipment.data.ac.uk/item/$id.html";

			# assumption-- everything from kitcat is equipment		
			$graph->addCompressedTriple( $our_uri, "rdf:type", "oo:Equipment" );

			$graph->addCompressedTriple( $our_uri, "http://id.equipment.data.ac.uk/ns/hasCode", $id, "literal" );
			$graph->addCompressedTriple( $our_uri, "http://id.equipment.data.ac.uk/ns/hasURI", "$our_uri", "literal" );
			$graph->addCompressedTriple( $our_uri, "http://id.equipment.data.ac.uk/ns/hasPage", "$url" );
			$graph->addCompressedTriple( $our_uri, "owl:sameAs", "$their_uri" );


			$graph->addCompressedTriple( $our_uri, "rdfs:label", $item["name"], "literal" );
			if($set['org']["org_idscheme"]=='ukprn'){
				$graph->addCompressedTriple( $our_uri, "oo:formalOrganization", "http://id.learning-provider.data.ac.uk/ukprn/".$set['org']["org_id"] );
			}
			if( $item["model"] != ""  || $item["manufacturer"] != "" )
			{
				$graph->addCompressedTriple( $our_uri, "gr:hasMakeAndModel", "$our_uri#model" );
				$graph->addCompressedTriple( "$our_uri#model", "rdf:type", "gr:ProductOrServiceModel" );
				if( $item["model"] != "" )
				{
					$graph->addCompressedTriple( "$our_uri#model", "rdfs:label", $item["model"], "literal" ); 
				}
				if( $item["manufacturer"] != "" )
				{
					$graph->addCompressedTriple( "$our_uri#model", "gr:hasManufacturer", "$our_uri#manu" );
					$graph->addCompressedTriple( "$our_uri#manu", "rdf:type", "gr:BusinessEntity" );
					$graph->addCompressedTriple( "$our_uri#manu", "rdfs:label", $item["manufacturer"], "literal" ); 
				}
			}

			if( $item["description"] != "" )
			{
				# kitcat always makes HTML fragment descriptions	
				$graph->addCompressedTriple( $our_uri, "dcterms:description", $item["description"], "http://purl.org/xtypes/Fragment-HTML" );
			}
		
			if( $item["contact1"] != "" )
			{	
				$graph->addCompressedTriple( $our_uri, "oo:contact", "$our_uri#contact1" );
				$graph->addCompressedTriple( $our_uri, "oo:primaryContact", "$our_uri#contact1" );
				$graph->addCompressedTriple( "$our_uri#contact1", "foaf:mbox", "mailto:".$item["contact1"] );
			}

			if( $item["contact2"] != "" )
			{	
				$graph->addCompressedTriple( $our_uri, "oo:contact", "$our_uri#contact2" );
				$graph->addCompressedTriple( "$our_uri#contact2", "foaf:mbox", "mailto:".$item["contact2"] );
			}

			if( $item["link"] != "" )
			{	
				$graph->addCompressedTriple( $our_uri, "foaf:page", $item["link"] );
			}

			if( $item["image"] != "" )
			{	
				$graph->addCompressedTriple( $our_uri, "foaf:depiction", $item["image"] );
			}
		}
		
		return $graph;
	}
	
	
	
	
	
	# clean up headers for uniquip
	function parse_uniquip_clean_header( $value )
	{
		# remove anything in brackets
		$value = preg_replace( "/\([^)]*\)/","", $value );

		# Remove leading and trailing whitespace
		$value = trim( $value );

		return $value;
	}
	
	/**
		Parses uniquip xls
		@return bool
		@param $set array
		@param $path string
	**/
	function parse_uniquip_xls($set,$path, &$notes){
		

		require_once( "{$this->config->pwd}/lib/PHPExcel/Classes/PHPExcel/IOFactory.php" );
		
		$graph = new eqGraphite();
		$row = 0;
		$thisset = array("ids"=>array());
		
		$objPHPExcel = PHPExcel_IOFactory::load($path);

		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

		$header = false;
		$data = array();
		foreach( $sheetData as $row )
		{
			# clean up whitespace
			foreach( $row as $key=>$value )
			{
				if( !$header ) { $value = $this->parse_uniquip_clean_header( $value ); }
				$row[$key] = trim( $value );
			}

			# skip entirely blank rows
			if( join( "",$row ) == "" ) { continue; }

			if(!$header)
			{
				$header = $row;
			}
			else
			{
				$line = array_combine($header, $row);
				$this->parse_uniquip_line($set,$line, $row, $notes, $graph);
			}
		}

		if( !$header )
		{
			$notes["errors"][] = "Failed to parse document";
		}

		
		return $graph;
		
	}
	
	
	/**
		Parses uniquip csv
		@return bool
		@param $set array
		@param $path string
	**/
	
	function parse_uniquip_csv($set,$path, &$notes){
		$graph = new eqGraphite();
		$row = 0;
		$thisset = array("ids"=>array());
		#Converts any mac csv into unix format
        $exec = "mac2unix -q ".escapeshellarg($path);
      	`$exec`;
		
		$starttime = microtime(true);

		if (($handle = fopen($path, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 4096, ",")) !== FALSE) {
		        if($row == 0){
					$titles = array();
					foreach($data as $k=>$dat){
						if(strlen($dat)==0){
							$dat = "f_".$k;
						}
						$titles[] = $dat;
					}
		        	

					$nooffeilds = count($titles);
				}else{
					$line = array_combine($titles,$data);
					if(count($line)!=$nooffeilds){
						$notes['warnings'][] = "Trouble parsing csv line {$row}\n";
					}else{
						$this->parse_uniquip_line($set,$line, $row, $notes, $graph);
					}
				}
				$row++;
		    }
		    fclose($handle);
		}
		
		$totaltime = microtime(true) - $starttime;
		
		echo "          ".number_format($totaltime / ($row-1),3). "s/line (".($row-1)." lines)\n";
		if($row<2){
			$notes['errors'][] = "No data in csv";
		}
		
		return $graph;
		
	}
	
	function parse_uniquip_line(&$set, &$line, &$row, &$notes, &$graph){

		$this->launch_db();
		$org = $set['org'];
		$item = array();
		
		if(isset($line['ID']) and strlen($line['ID'])){
			$item['item_id'] = md5($line['ID']);
		}else{
			$item['item_id'] = md5(join("|",$line));
		}
		
		$uri = "{$this->config->uribase}item/{$item['item_id']}";
		if( $graph->resource( $uri )->has( "rdfs:label" ) )
		{
			$notes['warnings'][] = "Item $row appears to be a duplicate";
			return false;
		}
		
		$item['item_org'] = $set['data_org'];
		$item['item_dataset'] = $set['data_uri'];
		
		if(isset($line['Location']) && strlen($line['Location'])){
			$luri = parse_url($line['Location']);
			if(isset($luri['host']) && $luri['host']=="en.wikipedia.org"){
				$page = explode("/",$luri['path'],3);
				$location = $this->location_extract("http://dbpedia.org/resource/{$page[2]}");
				$item['item_location'] = $location['loc_uri'];
			}
		}
		
		if(!isset($item['item_location']) || !$item['item_location']){
			$item['item_location'] = $org['org_location'];
		}
		
		$this->db->insert('items',$item,array("item_updated"=>"NOW()"),"REPLACE");
		
		$itemU = array("itemU_id"=>$item['item_id'], "itemU_org"=>$item['item_org'], "itemU_dataset"=>$item['item_dataset']);
		
		foreach($this->config->uniqupmap as $k=>$v){
			if(isset($line[$v])){
				$itemU["itemU_f_{$k}"] = $line[$v];
			}
		}
		if(isset($itemU['itemU_f_type']))
			$itemU['itemU_f_type'] = strtolower($itemU['itemU_f_type']);
		
		$this->db->insert('itemUniquips',$itemU,array("itemU_updated"=>"NOW()"),"REPLACE");
		
		//Start Build graph.
		$url = "http://{$this->config->host}/item/{$item['item_id']}.html";
		$graph->addCompressedTriple( $uri, "rdf:type", "oo:Equipment" );
		$graph->addCompressedTriple( $uri, "http://id.equipment.data.ac.uk/ns/hasCode", $item['item_id'], "literal" );
		$graph->addCompressedTriple( $uri, "http://id.equipment.data.ac.uk/ns/hasURI", $uri, "literal" );
		$graph->addCompressedTriple( $uri, "http://id.equipment.data.ac.uk/ns/hasPage", "$url" );	
	
		
		# any &amp; or &gt; will be decoded in the label
		$graph->addCompressedTriple( $uri, "rdfs:label", htmlspecialchars_decode($line["Name"]), "literal" );
		
		if($org["org_idscheme"]=='ukprn'){
			$graph->addCompressedTriple( $uri, "oo:formalOrganization", "http://id.learning-provider.data.ac.uk/ukprn/".$org["org_id"] );
		}
		
		# description assumed to contain HTML
		if( @$line["Description"] != "" )
		{
			$graph->addCompressedTriple( $uri, "dcterms:description", $line["Description"], "http://purl.org/xtypes/Fragment-HTML" );
		}
		if( @$line["ID"] != "" )
		{
			$graph->addCompressedTriple( $uri, "skos:notation", $line["ID"], "{$this->config->uribase}org/{$org['org_idscheme']}/{$org["org_id"]}/equipment-id-scheme" );
		}
		
		
		$contact1 = ( @$line["Contact Name"] != "" )
		         || ( @$line["Contact Telephone"] != "" )
		         || ( @$line["Contact Email"] != "" )
		         || ( @$line["Contact URL"] != "" );

		if( $contact1 )
		{	
			$graph->addCompressedTriple( $uri, "oo:contact", "$uri#contact1" );
			$graph->addCompressedTriple( $uri, "oo:primaryContact", "$uri#contact1" );
			if( @$line["Contact Name"] != "" )
			{
				$graph->addCompressedTriple( "$uri#contact1", "foaf:name", $line["Contact Name"], "literal" );
			}
			if( @$line["Contact URL"] != "" )
			{
				$graph->addCompressedTriple( "$uri#contact1", "foaf:page", $line["Contact URL"] );
			}
			if( @$line["Contact Telephone"] != "" )
			{	
				$graph->addPhone( "$uri#contact1", $line["Contact Telephone"] );
			}
			if( @$line["Contact Email"] != "" )
			{
				if(strcasecmp(substr($line["Contact Email"],0,7),"mailto:")!=0){
					$line["Contact Email"] = "mailto:".$line["Contact Email"];
				}
				$graph->addCompressedTriple( "$uri#contact1", "foaf:mbox", $line["Contact Email"]);
			}
		}



		$contact2 = ( @$line["Secondary Contact Name"] != "" )
		         || ( @$line["Secondary Contact Telephone"] != "" )
		         || ( @$line["Secondary Contact Email"] != "" )
		         || ( @$line["Secondary Contact URL"] != "" );

		if( $contact2 )
		{	
			$graph->addCompressedTriple( $uri, "oo:contact", "$uri#contact2" );
			if( @$line["Secondary Contact Name"] != "" )
			{
				$graph->addCompressedTriple( "$uri#contact2", "foaf:name", $line["Secondary Contact Name"], "literal" );
			}
			if( @$line["Secondary Contact URL"] != "" )
			{
				$graph->addCompressedTriple( "$uri#contact2", "foaf:page", $line["Secondary Contact URL"] );
			}
			if( @$line["Secondary Contact Telephone"] != "" )
			{
				$graph->addPhone( "$uri#contact2", $line["Secondary Contact Telephone"] );
			}
			if( @$line["Secondary Contact Email"] != "" )
			{
				$graph->addCompressedTriple( "$uri#contact2", "foaf:mbox", "mailto:".$line["Secondary Contact Email"] );
			}
		}



		if( @$line["Web Address"] != "" )
		{	
			$graph->addCompressedTriple( $uri, "foaf:page", $line["Web Address"] );
		}

		if( @$line["Photo"] != "" )
		{	
			$graph->addCompressedTriple( $uri, "foaf:depiction", $line["Photo"] );
		}

		if( @trim($line["Department"]) != "" )
		{
			$org_id = md5( $line["Department"] );
			$org_uri = "http://id.equipment.data.ac.uk/org/{$org['org_idscheme']}/{$org["org_id"]}/org/".rawurlencode($org_id);
			$graph->addCompressedTriple( $uri, "oo:organizationPart", $org_uri );
			$graph->addCompressedTriple( $org_uri, "rdfs:label", $line["Department"], "literal" );
			$graph->addCompressedTriple( $org_uri, "rdf:type", "http://www.w3.org/ns/org#Organization" );
			if($org["org_idscheme"]=='ukprn'){
				$graph->addCompressedTriple( "http://id.learning-provider.data.ac.uk/ukprn/".$org["org_id"], "org:hasSubOrganization", $org_uri );
			}
		}

		if( @trim($line["Building"]) != "" )
		{
			$org_id = md5( $line["Building"] );
			$org_uri = "http://id.equipment.data.ac.uk/org/{$org['org_idscheme']}/{$org["org_id"]}/org/".rawurlencode($org_id);
			$graph->addCompressedTriple( $uri, "oo:organizationPart", $org_uri );
			$graph->addCompressedTriple( $org_uri, "rdfs:label", $line["Building"], "literal" );
			$graph->addCompressedTriple( $org_uri, "rdf:type", "http://vocab.deri.ie/rooms#" );
			# we are *not* automatically assuming the building belongs to the uni, just in case
		}
		
		//If have used a wikipedia location
		if(isset($location['loc_uri']) && strlen($location['loc_uri'])){
			$graph->addCompressedTriple( $uri, "foaf:based_near", $location['loc_uri']);
		}

		# fields not yet handled:
		#"Type",
		#"Related Facility ID",
		#"Technique",
		#"ID",
		#"Site Location",
		#"Service Level",
		

	}
	
	function parse_graph_item(&$set,&$item,&$notes){
		$line = array();

		$contacts = array();
		$done = array();
		foreach( $item->all( "oo:primaryContact", "oo:contact" ) as $contact )
		{
			if( @$done[$contact->toString()] ) { continue; }
			$done[$contact->toString()] = true;
			$c = array();
			if( $contact->hasLabel() ) { $c["Name"] = (string)$contact->label(); }
			if( $contact->has( "foaf:mbox" ) ) { 
				$c["Email"] = preg_replace( "/mailto:/","", $contact->getString( "foaf:mbox" ) );
			}
			if( $contact->has( "foaf:phone" ) ) { 
				$c["Telephone"] = preg_replace( "/tel:/","", $contact->getString( "foaf:phone" ) );
			}
			$contacts []= $c;
		}


		$itemM['item_id'] = $this->misc_item_cacheid($item);
			
		#"Type",
			if( $item->isType( "oo:Equipment", "oldcerif:Equipment" ) )
			{
				$uniquip["Type"] = "equipment";
			}
			if( $item->isType( "oo:Facility", "oldcerif:Facility" ) )
			{
				$uniquip["Type"] = "facility";
			}
		#"Name",
			if( $item->hasLabel() )
			{
				$uniquip["Name"] = (string)$item->label();
			}
		#"Description",
			if( $item->has( "dcterms:description" ) )
			{
				$uniquip["Description"] = $item->getString( "dcterms:description" );
			}
		#"Related Facility ID",
		#"Technique",
		#"Location",
		#"Contact Name",
			@$uniquip["Contact Name"] = $contacts[0]["Name"];
		#"Contact Telephone",
			@$uniquip["Contact Telephone"] = $contacts[0]["Telephone"];
		#"Contact URL",
		#"Contact Email",
			@$uniquip["Contact Email"] = $contacts[0]["Email"];
		#"Secondary Contact Name",
			@$uniquip["Secondary Contact Name"] = $contacts[1]["Name"];
		#"Secondary Contact Telephone",
			@$uniquip["Secondary Contact Telephone"] = $contacts[1]["Telephone"];
		#"Secondary Contact URL",
		#"Secondary Contact Email",
			@$uniquip["Secondary Contact Email"] = $contacts[1]["Email"];
		#"ID",
			if( $item->has( "skos:notation" ) )
			{
				$uniquip["ID"] = $item->getString( "skos:notation" );
			}
		#"Photo",
			if( $item->has( "foaf:depiction" ) )
			{
				$uniquip["Photo"] = $item->getString( "foaf:depiction" );
			}
		#"Department",
		#"Site Location",
		#"Building",
		#"Service Level",
		#"Web Address",
			if( $item->has( "foaf:page" ) )
			{
				$uniquip["Web Address"] = $item->getString( "foaf:page" );
			}
		
		
		$itemM['item_org'] = $set['data_org'];
		$itemM['item_dataset'] = $set['data_uri'];
		
		/*if(strlen($line['Location'])){
			$luri = parse_url($line['Location']);
			if($luri['host']=="en.wikipedia.org"){
				$page = explode("/",$luri['path'],3);
				$location = $this->location_extract("http://dbpedia.org/resource/{$page[2]}");
				$item['item_location'] = $location['loc_uri'];
			}
		}*/
		if(!isset($itemM['item_location']) || !$itemM['item_location']){
			$itemM['item_location'] = $set['org']['org_location'];
		}
	
		$this->db->insert('items',$itemM,array("item_updated"=>"NOW()"),"REPLACE");

		$itemU = array("itemU_id"=>$itemM['item_id'], "itemU_org"=>$itemM['item_org'], "itemU_dataset"=>$itemM['item_dataset']);
	
		foreach($this->config->uniqupmap as $k=>$v){
			if(isset($uniquip[$v])){
				$itemU["itemU_f_{$k}"] = $uniquip[$v];
			}
		}	
	
		$itemU['itemU_f_type'] = strtolower($itemU['itemU_f_type']);
		$this->db->insert('itemUniquips',$itemU,array("itemU_updated"=>"NOW()"),"REPLACE");
		
	}
	
	function parse_graph(&$set,&$graph,&$notes, $type){
		

		$graph->ns( "oldcerif", "http://spi-fm.uca.es/neologism/cerif#" );
		
		$run = array("no_label_items"=>0,"no_label_or_desc_items"=>0, "no_contact_items"=>0);
		$items = array();
		foreach( $graph->allSubjects() as $resource )
		{
			if( $resource->isType( "oo:Facility", "oldcerif:Facility", "oo:Equipment", "oldcerif:Equipment" ))
			{

				if( !$resource->has( "rdfs:label" ) )
				{
					if( $resource->has( "dcterms:description" ) )
					{	
						# no label, use description
						$graph->addCompressedTriple( $resource, "rdfs:label", substr( $resource->get( "dcterms:description" ), 0, 30 )."...", "label" );
						$run["no_label_items"]++;
					}
					else	
					{
						$run["no_label_or_desc_items"]++;
						continue;
					}
				}
				
				
				
				if( !$resource->has( "oo:primaryContact", "oo:contact" ) )
				{
					$run["no_contact_items"]++;
					$con_uri = "$resource#contact1";
					$graph->addCompressedTriple( $resource, "oo:primaryContact", $con_uri );
					$graph->addCompressedTriple( $resource, "oo:contact", $con_uri );
					list( $scheme, $junk ) = preg_split( "/:/", $set["data_contact"] );
					if( $scheme == "tel" )
					{
						$graph->addPhone($con_uri, $set["data_contact"]  );
					}
					elseif( $scheme == "mailto" )
					{
						$graph->addCompressedTriple( $con_uri, "foaf:mbox", $set["data_contact"]  );
					}
					else
					{
						$graph->addCompressedTriple( $con_uri, "foaf:page", $set["data_contact"]  );
					}
				}
				$items[]=$resource;
			}
		}
		
		foreach( $items as $item )
		{
			if($type!='uniquip')
				$uniquip_row = $this->parse_graph_item($set,$item,$notes);
			$this->make_item_page($set,$item);
			$this->make_item_turtle($set,$item);			
		}
		
		
		if( $run["no_label_items"] )
		{		
			$notes["warnings"][] = $run["no_label_items"]." item".( $run["no_label_items"] == 1 ? "" : "s" )." had no label. Start of description used as label.";
		}
		if( $run["no_label_or_desc_items"] )
		{		
			$notes["warnings"][] = $run["no_label_or_desc_items"]." item".( $run["no_label_or_desc_items"] == 1 ? "" : "s" )." had no label OR description and could not be included.";
		}
		if( $run["no_contact_items"] )
		{		
			$notes["warnings"][] = $run["no_contact_items"]." item".( $run["no_contact_items"] == 1 ? "" : "s" )." had no contact listed and were set to the default contact."; 
		}
		
		
	}

	
	function save_graph_dataset(&$set, &$graph, &$notes){

		$org_cache_dir = "{$this->config->pwd}/htdocs/data/org";
		//First bit backwards compatiple (Only list the last dataset parsed for that organisation)
		# filename has ukprn- as a prefix as in the future there may be data from organisations
		# without a ukprn. Who can say for sure!
		$cache_file_ttl = "$org_cache_dir/{$set['org']['org_idscheme']}-{$set['org']['org_id']}.ttl";
		$cache_file_nt = "$org_cache_dir/{$set['org']['org_idscheme']}-{$set['org']['org_id']}.nt";
		
		$fh = fopen($cache_file_ttl, 'w') or die("can't open cache_file: $cache_file_ttl" );
		fwrite($fh, $graph->serialize( "Turtle" ) );
		fclose($fh);
	
		$fh = fopen($cache_file_nt, 'w') or die("can't open cache_file: $cache_file_nt" );
		fwrite($fh, @$graph->serialize( "NTriples" ) );
		fclose($fh);

		$cache_file_ttl = "$org_cache_dir/{$set['org']['org_idscheme']}-{$set['org']['org_id']}-{$set['data_hash']}.ttl";
		$cache_file_nt = "$org_cache_dir/{$set['org']['org_idscheme']}-{$set['org']['org_id']}-{$set['data_hash']}.nt";
		
		# cache graph if it's set and has some triples

		$fh = fopen($cache_file_ttl, 'w') or die("can't open cache_file: $cache_file_ttl" );
		fwrite($fh, $graph->serialize( "Turtle" ) );
		fclose($fh);
	
		$fh = fopen($cache_file_nt, 'w') or die("can't open cache_file: $cache_file_nt" );
		fwrite($fh, @$graph->serialize( "NTriples" ) );
		fclose($fh);
		
		if(isset($this->config->misc->megant)){
			$exec = "cat ".escapeshellarg($cache_file_nt)." >> ".escapeshellarg($this->config->misc->megant);
			exec($exec);
		}
		
		
	
	}
	
	function save_rdf($type){
		if($type == 'all'){
			$in = $this->config->misc->megant;
			$out = $this->config->pwd."/htdocs/data/equipment.ttl";
		}
		
		exec( "{$this->config->rapper->path} -e -i ntriples -o turtle ".escapeshellarg($in)." > ".escapeshellarg($out) );
		
	}
	function save_uniquip($type, &$info = array()){
		$orgs = array();
		$sets = array();
		$locs = array();
		
		$metadata = array();
		
		if($type == 'set'){
			$where =   array('item_dataset' => $info['data_uri']);
			$orgs[$info['data_org']] = $info['org'];
			$sets[$info['data_uri']] = $info;
			$fname = "{$this->config->pwd}/htdocs/data/org/{$info['org']['org_idscheme']}-{$info['org']['org_id']}-{$info['data_hash']}";		
		}elseif($type == 'all'){
			$where = array();
			$fname = "{$this->config->pwd}/htdocs/data/uniquip";	
		}
		
		
		$fp_csv = fopen("$fname.csv", 'w');
		$fp_tsv = fopen("$fname.tsv", 'w');
		$fp_json = fopen("$fname.json", 'w');
		
		fwrite($fp_json,"{\"records\":[");
		
		$loop = 1;
		$n = 0;
		$page = 0;
		$pagesize = 100;
		while($loop){
			$data = $this->db->fetch_many('`items` INNER JOIN `itemUniquips` ON `item_id` = `itemU_id`', $where, array(),"*","{$page},$pagesize");
			foreach($data as $rec){
				$line = array();
				foreach($this->config->uniqupmap as $k=>$v){
					$line[$v] = $rec["itemU_f_{$k}"];
				}
				
				if(!isset($locs[$rec['item_location']])){
					$loc = $this->get_location($rec['item_location']);
					$locs[$rec['item_location']] = "{$loc['loc_lat']} {$loc['loc_long']}";
				}
				
				if(!isset($orgs[$rec['item_org']])){
					$orgs[$rec['item_org']] = $this->get_org($rec['item_org']);
				}
				
				if(!isset($sets[$rec['item_dataset']])){
					$sets[$rec['item_dataset']] = $this->get_dataset($rec['item_dataset']);
				}

				$line['Institution Name'] = $orgs[$rec['item_org']]['org_name'];
				$line['Institution URL'] = $orgs[$rec['item_org']]['org_url'];
				$line['Institution Logo URL'] = $orgs[$rec['item_org']]['org_logo'];
				$line['Datestamp'] = date("c",strtotime($rec['item_updated']));
				$line['Approximate Coordinates'] = $locs[$rec['item_location']];
				$line['Corrections'] = $sets[$rec['item_dataset']]['data_corrections'];
				
				if($n==0){
					fputcsv ($fp_csv, array_keys($line));
					$this->misc_fputtsv($fp_tsv, array_keys($line));
				}
				
				fputcsv ($fp_csv, $line);
				$this->misc_fputtsv($fp_tsv, $line);

				$line['__URI'] = $this->config->uribase."item/".$rec['item_id'];
				$line['__ID'] = $rec['item_id'];
				
				if($n == 0){
					fwrite($fp_json,json_encode($line));	
				}else{
					fwrite($fp_json,",".json_encode($line));	
				}
				$n++;
			}
			$page+=$pagesize;
			if(count($data)!=$pagesize)
				$loop = 0;
		}

		$metadata['items'] = $n;
		fwrite($fp_json,"],\"metadata\":".json_encode($metadata)."}");

		fclose($fp_csv);
		fclose($fp_tsv);
		fclose($fp_json);
		
	}
	
	function make_item_turtle(&$set,&$item)
	{
	
		$ig = new Graphite();
		$item->g->graphCopy( $ig, $item );
		$done = array();
		
		if( $item->has( "gr:hasMakeAndModel" ) )
		{
			$make_and_model = $item->get( "gr:hasMakeAndModel" );
			$item->g->graphCopy( $ig, $make_and_model );
			if( $make_and_model->has( "gr:hasManufacturer" ) )
			{
				$manufacturer = $make_and_model->get( "gr:hasManufacturer" );
				$item->g->graphCopy( $ig, $manufacturer );
			}
		}
		foreach( $item->all( "oo:primaryContact", "oo:contact" ) as $contact )
		{
			if( @$done[$contact->toString()] ) { continue; }
			$done[$contact->toString()] = true;
			$item->g->graphCopy( $ig, $contact );
		}

		$itemid = $this->misc_item_cacheid( $item );
	
		$rdf =  $ig->serialize( "Turtle" );
		
		$this->db->insert('itemRDF',array("rdf_id"=>$itemid, "rdf_org"=>$set['data_org'],"rdf_dataset"=>$set['data_uri'],"rdf_rdf"=>$rdf ),array("rdf_updated"=>"NOW()"),"REPLACE");
	
		$file = $this->misc_item_cachepath($itemid, "ttl");
		file_put_contents( $file,$rdf ) or die("can't write file: $file" );
	}

	
	function save_status(){
		
		//V2 new status-v2.json
		$V1 = array();
		
		//V1 new status-v1.json
		$V2 = array();
		$V2['orgs'] = array();
		$V2['totals'] = array("orgs"=>0,"datasets"=>0,"items"=>0);
		
		$orgs = $this->db->fetch_many('orgs', array('org_ena' => 1, 'sort:0'=>'a:org_sort'), array());
		
		foreach($orgs as $org){
			
			//Tidy up
			unset($org['org_ena']);
			
			//Location
			$org['org_location'] = $this->location_fetch($org['org_location']);
			
			//Times
			$org['org_firstseen'] = date('c',strtotime($org['org_firstseen']));
			$org['org_lastseen'] = date('c',strtotime($org['org_lastseen']));
			if(isset($org['org_location']['loc_updated']))
				$org['org_location']['loc_updated'] = date('c',strtotime($org['org_location']['loc_updated']));
			
			
			//OPDs
			$org['org_opd'] = $this->db->fetch_one('autoOPDs', array('opd_id' => $org['org_uri'], 'opd_ena'=> 1), array());
			unset($org['org_opd']['opd_cache']);
						
			//datasets
			$org['org_datasets'] = array();
				
			$datasets = $this->db->fetch_many('`datasets` INNER JOIN `crawls` ON `data_crawl` = `crawl_id`', array('data_ena' => 1, 'data_org'=> $org['org_uri']), array());
			if(!count($datasets)) continue;
			
			foreach($datasets as $set){
				//tidy up vars
				unset($set['data_org']);
				unset($set['data_ena']);
				unset($set['data_crawl']);
				unset($set['crawl_id']);
				$set['crawl_notes'] = json_decode($set['crawl_notes'], true);
				$set['crawl_gong_json'] = json_decode($set['crawl_gong_json'], true);

				//Times
				$set['data_firstseen'] = date('c',strtotime($set['data_firstseen']));
				$set['data_lastseen'] = date('c',strtotime($set['data_lastseen']));
				$set['crawl_timestamp'] = date('c',strtotime($set['crawl_timestamp']));
			

				$org['org_datasets'][$set['data_hash']] = $set;
			
				$V2['totals']['datasets']++;
			
				$V2['totals']['items']+=$set['crawl_records'];
			
				//Legacy v1 status
				$line = array();
				foreach(array('org_ukprn'=>'org_id','org_idscheme'=>'org_idscheme', 'org_id'=>'org_id','org_name'=>'org_name', 'org_url'=>'org_url', 'org_logo'=>'org_logo') as $k=>$v){
					$line[$k] = $org[$v];
				}
				if($org['org_idscheme']!= 'ukprn') $line['org_ukprn'] = null;
			
				$line['dataset_type'] = array_search($set['data_conforms'],$this->config->conformsToMap);
				foreach(array('dataset_url'=>'data_uri','dataset_corrections'=>'data_corrections','dataset_contact'=>'data_contact', 'items'=>'crawl_records') as $k=>$v){
					$line[$k] = $set[$v];
				}
				foreach(array('org_easting'=>'loc_easting','org_northing'=>'loc_northing','org_lat'=>'loc_lat', 'org_long'=>'loc_long') as $k=>$v){
					$line[$k] = $org['org_location'][$v];
				}
				$line['errors'] = array(); 
				foreach($set['crawl_notes'] as $k=>$notes){
					foreach($notes as $note){
						$line['errors'][] = "{$k}: {$note}"; 
					}
				}
				$line['dataset_timestamp'] = strtotime($set['crawl_timestamp']);
				$V1[] = $line; 
				//END V1
			}
			
			
			$V2['orgs'][] = $org;
		
			$V2['totals']['orgs']++;	
		}
		
	
		
		file_put_contents("{$this->config->pwd}/htdocs/data/status-v1.json",json_encode($V1));
		file_put_contents("{$this->config->pwd}/htdocs/data/status-v2.json",json_encode($V2));

	}
	
	
	function make_item_page(&$set,&$item)
	{
	
		# create cache for displaying results
		$html = array();
		$html []= "<div class='images'>";
		if(isset($set['org']["org_logo"]) and strlen($set['org']["org_logo"])){
			$html []= "<a class='uni-logo' title='".$set['org']["org_name"]."' href='".$set['org']["org_url"]."'><img style='max-width:200px' src='/org/{$set['org']["org_idscheme"]}/{$set['org']["org_id"]}.logo?size=medium' /></a>";
		}
		
		if( $item->has( "foaf:depiction" ) )
		{
			$html []= "<img style='max-width:200px' src='".$item->get( "foaf:depiction" )."' />";
		}
		$html []= "</div>";
		if( $item->has( "foaf:page" ) )
		{
			$html []= "<p><a href='".$item->get("foaf:page")."'>More information</a>.</p>";
		}
		if( $item->has( "dcterms:description" ) )
		{
			$html []= "<div class='description'>".$item->get("dcterms:description")."</div>";
		}
		if( $item->has( "oo:organizationPart" ) && $item->get( "oo:organizationPart" )->hasLabel() )
		{
			$html []= "<div>Part of Organization: ".$item->get("oo:organizationPart")->label()."</div>";
		}
	#	foreach( $item->all( "dcterms:subject" ) as $subject )
	#	{
	#		$html []= "<div>Subject: ".$subject->label()."</div>";
	#	}
		
		if( $item->has( "gr:hasMakeAndModel" ) )
		{
			$make_and_model = $item->get( "gr:hasMakeAndModel" );
			if( $make_and_model->hasLabel() )
			{
				$html []= "<p>Model: ".$make_and_model->label()."</p>";
			}
			if( $make_and_model->has( "gr:hasManufacturer" ) )
			{
				$manufacturer = $make_and_model->get( "gr:hasManufacturer" );
				if( $manufacturer->hasLabel() )
				{
					$html []= "<p>Manufacturer: ".$manufacturer->label()."</p>";
				}
			}
		}
		$done=array();
		foreach( $item->all( "oo:primaryContact", "oo:contact" ) as $contact )
		{
			if( @$done[$contact->toString()] ) { continue; }
			$done[$contact->toString()] = true;
			$html []= "<p>Contact&nbsp;";
			if( $contact->hasLabel() ) { $html []= $contact->label()." "; }
			if( $contact->has( "foaf:mbox" ) ) {
				$html []= $contact->get( "foaf:mbox" )->prettyLink()." ";
			}
			if( $contact->has( "foaf:phone" ) ) {
				$html []= $contact->get( "foaf:phone" )->prettyLink()." ";
			}
			$html []= "</p>";
		}
		if( $set["data_corrections"] )
		{
			# use the graphite URL link renderer
			$g = new Graphite();
			$html []=  "<div class='corrections'>Issues with this record should be reported to ".$g->resource( $set["data_corrections"] )->prettyLink()."</div>";
		}

		$data = array( 
			"title"=> utf8_encode((string)$item->label() ),
			"content"=> utf8_encode( join("",$html) ) ) ;

		$itemid = $this->misc_item_cacheid( $item );
	
		
		$this->db->insert('itemPages',array("page_id"=>$itemid, "page_org"=>$set['data_org'],"page_dataset"=>$set['data_uri'],"page_title"=>(string)$item->label(),"page_content"=>&$data['content'] ),array("page_updated"=>"NOW()"),"REPLACE");
	
	
		$file = $this->misc_item_cachepath($itemid, "html");
		$fh = fopen($file, 'w') or die("can't open file: $file" );
		fwrite($fh, json_encode( $data ) );
		fclose( $fh );
	}	
	
	
	function misc_item_cacheid( $item )
	{
		if( !$item->has( "http://id.equipment.data.ac.uk/ns/hasCode" ) )
		{
			print $item->dumpText();
			print "missing hasCode";
			exit( 1 );
		}
		if( $item->getLiteral( "http://id.equipment.data.ac.uk/ns/hasCode" ) == "" )
		{
			print $item->dumpText();
			print "empty hasCode";
			exit( 1 );
		}
		return $item->getLiteral( "http://id.equipment.data.ac.uk/ns/hasCode" );
	}
	
	function misc_item_cachepath( $itemid, $suffix = false ){
		$path = "{$this->config->pwd}/var/item/".substr($itemid,0,2)."/".substr($itemid,2,2);
		if(!file_exists($path)){
			mkdir($path,0755,true);
		}
		$path .= "/".$itemid;
		if($suffix !== false) {
			$path .= ".".$suffix;
		}
		return $path;
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
	/**
		fetches a file using curl write (low memory)
		@return bool
		@param $url string
		@param $file string
		@param $follow bool
	**/
	function misc_curl_getfile($url, $file, $follow = true){
	
	
		$fp = fopen ($file, 'w+');//This is the file where we save the    information

		$curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 50);
		curl_setopt($curl, CURLOPT_FILE, $fp); // write curl response to file
		if($follow)
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	    $header = curl_exec($curl);
		curl_close($curl);
		fclose($fp);
		return $header;
		
	}
	
	function misc_fputtsv($fh, $data){
		$tsv_row = array();
		foreach( $data as $field )
		{
			$tsv_row []= preg_replace( "/[\r\t\n]+/"," ", $field );
		}
		fwrite( $fh, join( "\t", $tsv_row )."\n" );
	}
	
	function location_fetch($uri){
		
		if(!isset($this->cache->locations)){
			$this->cache->locations = array();
		}
		
		if(isset($this->cache->locations[$uri])){
			return $this->cache->locations[$uri];
		}
		
		$location = $this->db->fetch_one('locations', array('loc_uri' => $uri));
		if($location){
			unset($location['loc_point']); //remove mysql point;
			$this->cache->locations[$uri] = $location;
			return $location;
		}
		
		return false;
	}
	
	function location_extract($uri, $graph = false){
		
		if( strcasecmp(trim($uri),"http://dbpedia.org/resource/United_Kingdom") == 0){
			return false;
		}
		
		if(isset($this->cache->locations[(string)$uri])){
			return $this->cache->locations[(string)$uri];
		}
		

		if($graph===false){
			$graph = new eqGraphite();
			$graph->load( (string)$uri );
		}

		$g=$graph->resource((string)$uri);
			$config_item = array();
			
		if($g->has( "geo:lat" ) and $g->has( "geo:long" )){
			$this->cache->locations[(string)$uri] = $this->location_find($g);
			return $this->cache->locations[(string)$uri];
		}
		if(($loc = $g->get( "foaf:based_near" ))!="[NULL]"){
			$this->cache->locations[(string)$uri] = $this->location_find($loc);
			return $this->cache->locations[(string)$uri];
		}
		if(($loc = $g->get( "http://data.ordnancesurvey.co.uk/ontology/postcode/postcode" ))!="[NULL]"){
			$this->cache->locations[(string)$uri] = $this->location_find_rdf($loc);
			return $this->cache->locations[(string)$uri];
		}
		$sameas = $graph->resource( $uri )->all( "http://www.w3.org/2002/07/owl#sameAs" );	
		$uris = array();
		foreach($sameas as $same){
			$uria = parse_url($same);
			$uria['uri'] = (string)$same;
			$uris[$uria['host']] = $uria;
		}
		if(isset($uris['id.learning-provider.data.ac.uk'])){
			$gid = new eqGraphite();
			$gid->load( $uris['id.learning-provider.data.ac.uk']['uri']);
			if(($loc = $gid->resource($uris['id.learning-provider.data.ac.uk']['uri'])->get( "http://data.ordnancesurvey.co.uk/ontology/postcode/postcode" ))!="[NULL]"){
				$this->cache->locations[(string)$uri] = $this->location_find_rdf($loc);
				return $this->cache->locations[(string)$uri];
			}
		}
		if(isset($uris['dbpedia.org'])){
			 $this->cache->locations[(string)$uri] = $ret = $this->location_find_rdf($uris['dbpedia.org']['uri']);
			 return $this->cache->locations[(string)$uri];
		}
		
		return false;

	}

	function location_find_rdf($loc){
		$g = new eqGraphite();
		$g->load( (string)$loc );
		return $this->location_find($g->resource((string)$loc));
	}
		
	function location_find($loc){
		$loc->g->load((string)$loc);
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
	
	function uniquipFields()
	{
		return array( 
	"Type",
	"Name",
	"Description",
	"Related Facility ID",
	"Technique",
	"Location",
	"Contact Name",
	"Contact Telephone",
	"Contact URL",
	"Contact Email",
	"Secondary Contact Name",
	"Secondary Contact Telephone",
	"Secondary Contact URL",
	"Secondary Contact Email",
	"ID",
	"Photo",
	"Department",
	"Site Location",
	"Building",
	"Service Level",
	"Web Address",

	"Institution Name",
	"Institution URL",
	"Institution Logo URL",
	"Datestamp",
	"Approximate Coordinates",
	"Corrections",
	);
	}
	
	function messageFromTemplate($template, $to, &$feilds, $type, $link = ""){
		
		if(!file_exists("{$this->config->pwd}/etc/messages/{$template}.xml")) return false;
		
		$template = simplexml_load_file("{$this->config->pwd}/etc/messages/{$template}.xml");
		
		$this->messageFromTemplate_feilds = $feilds;
		
		$subject = $this->messageFromTemplateProcess((string)$template->subject);
		$body = $this->messageFromTemplateProcess((string)$template->body);

		if($type == 'alert'){
			 $this->messageAlert($to, $subject, $body, $link);
		}else{
			$this->messageSend($to, $subject, $body, $type, $link);
		}
		
	}


    function messageFromTemplateProcess($text){
       $reg = "/\{\{([0-9a-zA-Z\-\| \_]+)\}\}/";
       return preg_replace_callback($reg, "self::messageFromTemplateReplace", $text);
     }
  
     function messageFromTemplateReplace($matches){
     	 if($matches[1] == 'signature'){
			 return file_get_contents("{$this->config->pwd}/etc/messages/signature.txt");
     	 }else{
			 $rep = explode("|",$matches[1]);
			 $count = count($rep);
			 if(isset($this->messageFromTemplate_feilds[$rep[0]])){
				 if($count == 1){
					 return $this->messageFromTemplate_feilds[$rep[0]];
				 }else{
					 $ret = $this->messageFromTemplate_feilds[$rep[0]];
					 for($i=1;$i<$count;$i++){
						  if(!isset($ret[$rep[$i]])){
							  return "";
						  }
					 	 $ret = $ret[$rep[$i]];
					 }
					 return $ret;
				 }
			 }
				
			 
			 
     	 }
		 return "";
     }  
	

	function messageAlert($to, $subject, $body, $link = ""){
		
		$this->launch_db();
		$old = $this->db->fetch_one('messages', array('message_type' => 'alert', 'message_link'=>$link), array('`message_time`' => ">:DATE_SUB(NOW(),INTERVAL {$this->config->maxcahceage} SECOND)"));
		if($old !== false)
			return false; //Skip if message has been sent 2 weeks ago;
		
		
		return $this->messageSend($to, $subject, $body, "alert", $link);
		
		
	}
	
	
	function messageSend($to, $subject, $body, $type = "single", $link = ""){
		
		$msg['message_to'] = $to;
		$msg['message_subject'] = $subject;
		$msg['message_body'] = $body;
		$msg['message_type'] = $type;
		$msg['message_link'] = $link;
		$msg['message_sent'] = 1;
		
		$headers = "From: {$this->config->messages->from}\r\n" .
		    "Reply-To: {$this->config->messages->from}\r\n" .
		    "BCC: ". join(", ", $this->config->crawler->emailto) ."\r\n" .
		    'X-Mailer: Equipment.Data (PHP/' . phpversion().")";

		mail($to, $subject, $body, $headers);	
		
		$this->launch_db();
		$this->db->insert('messages', $msg, array('message_time'=>"NOW()"));	
		
	}
	
	
}

class eqGraphite extends graphite{
	function __construct() {
		parent::__construct();
		global $eq;
		$this->cacheDir($eq->config->cachepath."/graphite");
	}
	
	
	function cloneGraphWithAliases( $aliases )
	{
		$g2 = new eqGraphite();

		$triples = $this->toArcTriples();
		foreach( $triples as &$t )
		{
			if( @$aliases[$t["s"]] ) { $t["s"] = $aliases[$t["s"]]; }
			if( @$aliases[$t["p"]] ) { $t["p"] = $aliases[$t["p"]]; }

			if( $t["o_type"] == "literal" )
			{
				if( @$aliases[$t["o_datatype"]] ) { $t["o_datatype"] = $aliases[$t["o_datatype"]]; }
			}

			if( $t["o_type"] == "resource" )
			{
				if( @$aliases[$t["o"]] ) { $t["o"] = $aliases[$t["o"]]; }
			}
		}

		foreach( $aliases as $from=>$to )
		{
			$triples []= array( 
				"s" => $to,
				"p" => "http://www.w3.org/2002/07/owl#sameAs",
				"o" => $from,
				"o_type" => "resource" );
		}
		$g2->addTriples( $triples );
		return $g2;
	}
	
	# Copys a graph into anoter using item
	function graphCopy( $g2, $item )
	{
		foreach( $item->relations() as $rel )
		{
			if( $rel->nodeType() != "#relation" ) { continue; }

			foreach( $item->all( $rel ) as $obj )
			{
				$datatype = $obj->datatype();
				if( @!$datatype && $obj->nodeType() == "#literal" ) { $datatype = "literal"; }
				$g2->addTriple( "$item", "$rel", "$obj", $datatype, $obj->language() );
			}
		}
	}
	
	
	# adds a foaf:phone number to the URI in the $graph, but tries to do some sensible things.
	# 
	function addPhone( $uri, $phone_number )
	{
		# remove whitespace
		$phone_number = preg_replace( '/ /', '', $phone_number );

		# remove (0) 
		$phone_number = preg_replace( '/\(0\)/', '0', $phone_number );
		
		#remove no digits
		$phone_number = preg_replace( '/[^0-9]/', '', $phone_number );

		# replace leading 0 with +44 (UK code). 
		$phone_number = preg_replace( '/^0/', '+44', $phone_number );

		# if it contains weird characters, make it a literal, otherwise a tel: resource
		if( preg_match( '/[^+\d]/', $phone_number  ) )
		{
			$this->addCompressedTriple( $uri, "foaf:phone", $phone_number, "literal" );
		}
		else
		{
			$this->addCompressedTriple( $uri, "foaf:phone", "tel:".$phone_number );
		}
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
		return $this->lastinsertid();
	}
	
	function where($params, $paramsraw, &$query, &$infields, &$i, &$orderby = array()){		
		
		$orderby = array();
		
		foreach($params as $k=>$v){
			if(substr($k,0,5)=='sort:'){
				if(substr($v,0,2)=="a:"){
					$orderby[] = substr($v,2)." ASC";
				}elseif(substr($v,0,2)=="d:"){
					$orderby[] = substr($v,2)." DESC";
				}else{
					$orderby[] = "$v ASC";
				}
			}elseif(substr($v,0,2)=="<:"){
				$query[] = "`$k` < ?";
				$infields[$i] = substr($v,2);
			}elseif(substr($v,0,2)==">:"){
				$query[] = "`$k` > ?";
				$infields[$i] = substr($v,2);
			}elseif(substr($v,0,2)=="!:"){
				$query[] = "`$k` != ?";
				$infields[$i] = substr($v,2);
			}else{
				$query[] = "`$k` = ?";
				$infields[$i] = $v;
			}
			
			$i++;
		}
		foreach($paramsraw as $k=>$v){
			
			if(substr($v,0,2)=="<:"){
				$query[] = "$k < ".substr($v,2);
			}elseif(substr($v,0,2)==">:"){
				$query[] = "$k > ".substr($v,2);
			}elseif(substr($v,0,2)=="!:"){
				$query[] = "$k != ".substr($v,2);
			}else{
				$query[] = "$k = $v";
			}
		}
		
		if(count($query)==0){
			$query[] = "1";
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
				
		$this->where($params, $paramsraw, $query, $infields, $i, $orderby);
	
		$sql = "UPDATE {$table} SET ".join(", ",$fieldsup)." WHERE ".join(" AND ", $query);
		
		if(count($orderby))
			$sql .= "ORDER BY ".join(", ",$orderby);
		
		if($limit)
			$sql .= " Limit ".$limit;

		return $this->exec($sql, $infields);
	}
	

	
	
	function delete($table, $params = array(), $paramsraw = array(), $limit = false){
		$i = 1;
		$query = array();
		$infields = array();
	
		
		if($this->dryrun){
			echo "SQL: Delete from: $table\n";
			foreach(array(&$params,&$paramsraw) as $a){
				foreach($a as $k=>$v){
					$query[] = "`$k` = $v";
				}
			}
			echo "\t\tWHERE ".join(" AND ", $query)."\n";
			return true;
		}
		
		
		$this->where($params, $paramsraw, $query, $infields, $i);
		
		$sql = "DELETE FROM {$table} WHERE ".join(" AND ", $query);
		
		if($limit)
			$sql .= " Limit ".$limit;

		return $this->exec($sql, $infields);
	}
	
	
	function fetch_many($table, $params = array(), $paramsraw = array(), $what = "*", $limit = false){
		$i = 1;
		$query = array();
		$infields = array();
		
		$this->where($params, $paramsraw, $query, $infields, $i,$orderby);
		
		$sql = "SELECT {$what} FROM {$table} WHERE ".join(" AND ", $query);
		if(count($orderby))
			$sql .= " ORDER BY ".join(", ",$orderby);
		
		if($limit)
			$sql .= " Limit ".$limit;

		$this->lastsql = $sql;

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
