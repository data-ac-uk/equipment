<?php
class search {

	function fragment() {
                $f3=Base::instance();
		global $_GET;
		$results = search::render( search::perform( $_GET["term"] ) );
		if( sizeof($results) == 0 )
		{
			print "<p style='margin-top:1em'>No matches.</p><p>Tip: If you are  trying out the system, 'laser' or 'microscope' return plenty of results.</p>";
			return;
		}
		print "<div>".count($results)." matches.</div>";		
		print join( "", $results );
	}

	static function perform( $q ) {

		if( trim($q) == "" )
		{
			return array();
		}
		$units = "km";
		if( @trim($_GET["units"])== "miles" )
		{
			$units = "miles";
		}
		$loc = false;
		$sort = @trim($_GET["sort"]);
		if( $sort != "" )
		{
			list( $e,$n ) = preg_split( '/,/', $sort );
			$loc = true;
		}
		
		$f3=Base::instance();
		 
		$eq = $f3->eq;
		$eq->launch_db();
		
		$sql_from = "FROM itemUniquips
		INNER JOIN `items` ON `itemU_id` = `item_id`
			INNER JOIN `orgs` ON `itemU_org` = `org_uri`";
	
		if( $loc ){
			$sql_from .= " RIGHT OUTER JOIN `locations` ON item_location = loc_uri";	
		}
		
		
		if(0){
			$sql_sel = "SELECT *, MATCH(`itemU_f_name` ,  `itemU_f_desc` ,  `itemU_f_technique`) AGAINST (?  IN BOOLEAN MODE) as score ";
			$sql_where = " WHERE MATCH(`itemU_f_name` ,  `itemU_f_desc` ,  `itemU_f_technique`) AGAINST (? IN BOOLEAN MODE) ORDER BY `score` DESC ";
			$sql_params = array(1=>$q,2=>$q);
		}else{
			$sql_where = " WHERE `itemU_f_name` LIKE ? OR `itemU_f_desc` LIKE ? OR `itemU_f_technique` LIKE ? ";
			$sql_sel = "SELECT * ";
			$sql_params = array(1=>"%{$q}%",2=>"%{$q}%",3=>"%{$q}%");
		}

	
		$res = $eq->db->exec("{$sql_sel} {$sql_from} {$sql_where}", $sql_params);
		
		$i = 0;
		
		$results = array();
		foreach($res as $line){
			
			$key = sprintf( "%10d", $i );
			$dinfo = NULL;
			
			if( $loc ){
				if(strlen($line['loc_uri']) )
				{
					$dist = round(sqrt( pow($line['loc_easting']-$e,2) + pow($line['loc_northing']-$n,2)));
					$key = sprintf( "%10d", $dist ).$key;
				
					if( $units == "miles" ) { $dist *= 0.621371192; }
					$dinfo = (round( $dist / 100 )/10)." ".$units;
				}else{
					$key = "9999999999".$key;
					$dinfo = NULL;
				}
			}
			
			$results[$key] = array(
				"item_code"=>$line['item_id'],
				"item_title"=>$line['itemU_f_name'],
				"dist"=>$dinfo,
				"org_name"=>$line['org_name']
			);
			
			$i++;
		}
		
		if( $loc ){
			ksort( $results );	
		}
		
		return $results;
		
	}


	static function data()
	{	
		// search API v0.1
		
		header( "Content-type: text/plain" );

                $f3=Base::instance();
		global $_GET;
		$q = @$_GET["q"];
		$results = search::perform( $q );
		
		if( sizeof($results) == 0 )
		{
			print "<p style='margin-top:1em'>No matches.</p><p>Tip: If you are  trying out the system, 'laser' or 'microscope' return plenty of results.</p>";
			return;
		}
		$base_url = "http://equipment.data.ac.uk/";

		$feed_url = $base_url."data/search?q=".urlencode( $q );
		$rgraph = new Graphite();
		$rgraph->ns( "rss", "http://purl.org/rss/1.0/" );
		$rgraph->addCompressedTriple( $feed_url, "rdf:type", "rss:channel" );
		$rgraph->addCompressedTriple( $feed_url, "rss:title", "Equipment data query", "literal" );
		$rgraph->addCompressedTriple( $feed_url, "rss:link", $feed_url, "literal" );
		$rgraph->addCompressedTriple( $feed_url, "dc:date", gmdate( "c" ), "literal" );
		foreach( $results as $result )
		{
			$uri = "http://id.equipment.data.ac.uk/item/".$result["item_code"];
			$rgraph->addCompressedTriple( $uri, "rdf:type", "rss:item" );
			$ttl_url = "../var/item/".$result["item_code"].".ttl";
			$rgraph->load( $ttl_url );
		}
		print $rgraph->serialize( "RDFXML" );
	}

	static function render( $results )
	{
		$r = array();
		foreach( $results as $result )
		{
			$row = "
	<a class='search-result' onclick='show_result(\"".$result["item_code"]."\", ".json_encode($result["item_title"])."); 
		return false;' href='/item/".$result["item_code"].".html'>
           <span class='result-title'>".$result["item_title"]."</span>
           <span class='result-info'>".$result["org_name"].(@$result["dist"]?" - ".$result["dist"]:"")."</span>
        </a>
";
			$r []= $row;
		}
		return $r;
	}
}
		
