<?php
require_once( "../lib/arc2/ARC2.php" );
require_once( "../lib/Graphite/Graphite.php" );

date_default_timezone_set( "Europe/London" );
try {
    $f3=require('lib/base.php');
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

#if ((float)strstr(PCRE_VERSION,' ',TRUE)<7.9)
#	trigger_error('Outdated PCRE library version');

if (function_exists('apache_get_modules') &&
	!in_array('mod_rewrite',apache_get_modules()))
	trigger_error('Apache rewrite_module is disabled');

$f3->set('DEBUG',3);
$f3->set('AUTOLOAD',"app/");
$f3->set('UI','ui/');

if( file_exists( "ui/note.html" ) )
{
	$note = Template::instance()->render( "note.html" );
	$f3->set( "note", $note );
}


$f3->route('GET /faq',
	function() use($f3) {
                $f3=Base::instance();

		$f3->set('html_title', "Frequently Asked Questions" );
		$f3->set('content','faq.html');
		print Template::instance()->render( "page-template.html" );
	}
);
$f3->route('GET /uniquip',
	function() use($f3) {
                $f3=Base::instance();

		$f3->set('html_title', "UNIQUIP Data Publishing Specification" );
		$f3->set('content','uniquip.html');
		print Template::instance()->render( "page-template.html" );
	}
);
$f3->route('GET /',
	function() use($f3) {
                $f3=Base::instance();

		$q = "";
		if( @$_GET["q"] ) { $q = $_GET["q"]; }
		$f3->set('q', $q );
		$search = Template::instance()->render( "search-form.html" );

		if( $q != "" )
		{
			require_once( "app/search.php" );
			$results = search::perform( $_GET["q"] );
			if( sizeof($results) == 0 )
			{
				$search .= "<p>No matches</p>";
			}
			$search .= "<div id='results-container'>";
  			$search .= "  <div id='results' class='sixteen columns'>";
			$search .= "    <div>".count($results)." matches.</div>";		
			$search .=      join( "", $results );
			$search .= "  </div>";
			$search .= "</div>";
		}
		else
		{
			$search .= "<div id='results-container' style='display:none'>";
  			$search .= "  <div id='results' class='eight columns'></div>";
  			$search .= "  <div id='featured-result' class='eight columns'></div>";
			$search .= "</div>";
			# only do js on a javascript version of the UI
			$search .= "<script src='/resources/quick-search.js.php' ></script>";
		}
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
);
$f3->route('GET /status', 'status->page' );
$f3->route('GET /search', 'search->fragment' );
$f3->route('GET	/item/@id.html', 'item->page' );
$f3->route('GET /item/@id.fragment', 'item->fragment' );

$f3->run();
exit;
