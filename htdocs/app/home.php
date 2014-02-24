<?php
class home {

	function page() 
	{
                $f3=Base::instance();



		$status = json_decode( file_get_contents( 'data/status-v2.json' ), true );
		

		$f3->set('status', $status );
		
		$remote_addr = $_SERVER['REMOTE_ADDR'];
		$hostname = gethostbyaddr($remote_addr);

		$defaultsort = "";

		if( preg_match( "/([a-z0-9-]+\.ac\.uk)$/", $hostname, $r ) )
		{
			$domain = $r[1];
			$rows = file( "../var/learning-providers-plus.tsv" );
			foreach( $rows as $line )
			{
				$cells = preg_split( "/\t/", $line );
				if( $cells[9] == "http://www.".$domain."/" )
				{
					$defaultsort = $cells[1];
				}
			}
		}

		$q = "";
		if( @$_GET["q"] ) { $q = $_GET["q"]; }
		$f3->set('q', $q );
		$f3->set('defaultsort', $defaultsort );
		$search =  <<<END
	<script>
		$(function() {
			$( ".search-div .search-cancel" ).show();
       		$('#qs-overlay').show();
			 $( ".search-div .search-cancel" ).click(function() {
				 location.href="/";
			});
		});
	</script>
END;
		$search .= "<div id=\"searchbox\">";
		$search .= Template::instance()->render( "search-form.html" );

		if( $q != "" )
		{
			require_once( "app/search.php" );
			$results = search::render( search::perform( $q ) );
			$search .= "<div id='results-container'>";
  			$search .= "  <div id='results'>";
			if( sizeof($results) == 0 )
			{
				$search .= "<p>No matches</p>";
			}
			else
			{
				$search .= "<div>".count($results)." matches.</div>";		
				$search .= join( "", $results );
			}
			$search .= "  </div>";
			$search .= "</div>";
		}
		else
		{
			$search .= "<div id='results-container' style='display:none'>";
  			$search .= "  <div id='results' class='eight columns'></div>";
  			$search .= "  <div id='featured-result' class='seven columns'></div>";
			$search .= "  <div class='clear'> </div>";
			$search .= "</div>";
			# only do js on a javascript version of the UI
			$search .= "<script src='/resources/quick-search.js.php' ></script>";
		}
		$search .= "</div>";
		
		$f3->set('search', $search );


		$status = json_decode( file_get_contents( 'data/status.json' ), true );
		$logos = array();
		foreach( $status as $feed )
		{
			$logos []= "<a href='".$feed["org_url"]."'><img src='".$feed["org_logo"]."' /></a>";
		}
		$f3->set('logos', join( " ", $logos ) );
		$f3->set('html_title', "UK University Facilities and Equipment Open Data" );
		$f3->set('content','homepage.html');
		print Template::instance()->render( "page-template.html" );
	}
}

