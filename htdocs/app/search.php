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
		$sort = @trim($_GET["sort"]);
		if( $sort != "" )
		{
			list( $e,$n ) = preg_split( '/,/', $sort );
		}
		$lines = file( "../var/search.tsv" );
		$terms = preg_split( '/\s+/', $q );
		$results = array();
		$titles = array();
		foreach( $lines as $line )
		{
			#nb. This scans the line including the  md5 but is pretty 
			# unlikely to produce false positives as a result
			foreach( $terms as $term )
			{
				if( !preg_match( '/\b'.$term.'/i', $line ) ) { continue 2; }
			}
			$line = chop( $line );
			@list( $words,$code,$title,$org,$e2,$n2) = preg_split( '/\t/', $line );
		
			$key = strtoupper($title).$code;
			$dinfo = "";
			if( @$e )
			{
				$dist = round(sqrt( ($e2-$e)*($e2-$e) + ($n2-$n)*($n2-$n) ));
				$key = sprintf( "%10d", $dist ).$key;
				
				if( $units == "miles" ) { $dist *= 0.621371192; }
				$dinfo = (round( $dist / 100 )/10)." ".$units;
			}
				
			$results[$key] = array(
				"item_code"=>$code,
				"item_title"=>$title,
				"dist"=>$dinfo,
				"org_name"=>$org,
			);
		}

		ksort( $results);

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

		$feed_url = $base_url."/data/search?q=".urlencode( $q );
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
	<a class='search-result' onclick='show_result(\"".$result["item_code"]."\"); 
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
		
