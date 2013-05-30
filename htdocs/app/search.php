<?php
class search {

	function fragment() {
                $f3=Base::instance();
		global $_GET;
		$results = search::perform( $_GET["term"] );
		if( sizeof($results) == 0 )
		{
			print "<p>No matches</p>";
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
			list( $words,$code,$title,$org,$e2,$n2) = preg_split( '/\t/', $line );
		
			$key = strtoupper($title).$code;
			$dinfo = "";
			if( @$e )
			{
				$dist = round(sqrt( ($e2-$e)*($e2-$e) + ($n2-$n)*($n2-$n) ));
				$key = sprintf( "%10d", $dist ).$key;
				
				if( $units == "miles" ) { $dist *= 0.621371192; }
				$dinfo = " - ". (round( $dist / 100 )/10)." ".$units;
			}
				
			
			$results [$key] =
				"<a class='search-result' onclick='show_result(\"$code\"); return false;' href='/item/$code.html'>"
				."<span class='result-title'>$title</span>"
				."<span class='result-info'>".$org." $dinfo</span>"
				."</a>";
		}

		ksort( $results);
		return $results; 
	}
}
		
