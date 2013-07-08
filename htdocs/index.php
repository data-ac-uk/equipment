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

$note = "";
if( file_exists( "ui/note.html" ) )
{
	$note = Template::instance()->render( "note.html" );
}
$f3->set( "note", $note );


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
$f3->route('GET /poster',
	function() use($f3) {
                $f3=Base::instance();

		$f3->set('html_title', "Poster" );
		$f3->set('content','poster.html');
		print Template::instance()->render( "page-template.html" );
	}
);
$f3->route('GET /', 'home->page' );
$f3->route('GET /status', 'status->page' );
$f3->route('GET /search', 'search->fragment' );
$f3->route('GET	/org/@id', 'org->page' );
$f3->route('GET	/item/@id', 'item->page' );
$f3->route('GET /item/@id.fragment', 'item->fragment' );

$f3->run();
exit;
