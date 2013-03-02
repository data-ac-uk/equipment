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

$f3->route('GET /',
	function() use($f3) {
                $f3=Base::instance();

		$f3->set('html_title', "Equipment.data.ac.uk" );
		$f3->set('content','coming-soon.html');
		print Template::instance()->render( "page-template.html" );
	}
);
$f3->route('GET /index2.php',
	function() use($f3) {
                $f3=Base::instance();

		$status = json_decode( file_get_contents( 'data/status.json' ), true );
		$logos = array();
		foreach( $status as $feed )
		{
			$logos []= "<a href='".$feed["org_url"]."'><img src='".$feed["org_logo"]."' /></a>";
		}
		$f3->set('logos',join( " ", $logos ) );
		$f3->set('html_title', "UK University Facilities and Equipment Open Data" );
		$f3->set('content','homepage.html');
		print Template::instance()->render( "page-template.html" );
	}
);
$f3->route('GET /status', 'status->page' );


$f3->run();
exit;
