<?php
require_once( "../../lib/arc2/ARC2.php" );
require_once( "../../lib/Graphite/Graphite.php" );
require_once( "../../lib/OPDLib/OrgProfileDocument.php" );
require_once( "ResourceVerifier.php" );

date_default_timezone_set( "Europe/London" );
try {
    $f3=require('../lib/base.php');
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
$f3->set('DEBUG',3);
$f3->set('UI','ui/');

if( @!$_GET["opd"] && @!$_GET["homepage"] && @!$_POST["opd_paste"])
{
	$f3->set('html_title', "Organisation Profile Document (OPD) Checker" );
	$f3->set('content','opd-intro.html');
	$f3->set('note','' );
	print Template::instance()->render( "page-template.html" );
	exit;
}

$homepage_url = @$_GET["homepage"];
$opd_url = @$_GET["opd"];
$opd_paste = @$_POST["opd_paste"];

$content = array();

try 
{
	if( @$opd_paste )
	{
		$content []= "<p>Attempting to parse OPD pasted form.</p>";
		$opd = OrgProfileDocument::from_string( $opd_paste );
	}
	elseif( @$homepage_url )
	{
		$content []= "<p>Attempting to autodiscover OPD from <a href='$homepage_url'>$homepage_url</a></p>";
		$opd = OrgProfileDocument::discover( $homepage_url );
	}
	else
	{
		$content []= "<p>Attempting to load OPD from <a href='$opd_url'>$opd_url</a></p>";
		$opd = new OrgProfileDocument( $opd_url );
	}
}
catch( OPD_Discover_Exception $e )
{
	$content[] = "<h2>Failed to discover OPD</h2><p>".$e->getMessage()."</p>";
	$f3->set('results', join( "", $content ) );
	serve_results();
	exit;
}
catch( OPD_Load_Exception $e )
{
	$content[] = "<h2>Failed to load OPD</h2><p>".$e->getMessage()."</p>";
	$f3->set('results', join( "", $content ) );
	serve_results();
	exit;
}
catch( OPD_Parse_Exception $e )
{
	$content[] = "<h2>Failed to parse OPD</h2><p>".$e->getMessage()."</p>";
	$content[] = "<div class='code'>".htmlspecialchars(htmlspecialchars( $e->document ))."</div>";
	$f3->set('results', join( "", $content ) );
	serve_results();
	exit;
}
catch( Exception $e ) 
{
	$content[] = "<h2>Error</h2><p>".$e->getMessage()."</p>";
	$f3->set('results', join( "", $content ) );
	serve_results();
	exit;
}



$content []= "<p>OPD Loaded OK!</p>";

$content []= "<p>Organisation self-assigned URI is $opd->org</p>";

$rv = new ResourceVerifier( "opd_verify.json" );

$content []= "<p>The following content was identified:</p>";

$content []= "<h2>Core</h2>";

$opd->graph->ns( "vcard", "http://www.w3.org/2006/vcard/ns#" );
$content []= $rv->html_report( "core", $opd->org );

$bits = array(
	array( 
		"name"=>"Facilities Dataset",
		"subjects"=>array("http://purl.org/openorg/theme/facilities"),
		"verify"=>array("dataset"),
	),
	array( 
		"name"=>"Equipment Dataset",
		"subjects"=>array("http://purl.org/openorg/theme/equipment"),
		"verify"=>array("dataset"),
	),
	array( 
		"name"=>"Members Dataset",
		"subjects"=>array("http://purl.org/openorg/theme/members"),
		"verify"=>array("dataset"),
	),
	array( 
		"name"=>"Events Dataset",
		"subjects"=>array("http://purl.org/openorg/theme/events"),
		"verify"=>array("dataset"),
	),
	array( 
		"name"=>"Places Dataset",
		"subjects"=>array("http://purl.org/openorg/theme/places"),
		"verify"=>array("dataset"),
	),
);
	

foreach( $bits as $bit )
{
	$datasets = $opd->datasets( $bit["subjects"] );
	if( ! sizeof($datasets ) ) { continue; }
	
	$content []= "<h2>".$bit["name"]."</h2>";
	
	$n = 1;
	foreach( $datasets as $dataset )
	{
		$content []= "<div class='opd_dataset'>";
		$content []= "<h3>".$bit["name"]." #$n</h3>";
		$n++;
		foreach( $bit["verify"] as $vsection )
		{
			$content []= $rv->html_report( $vsection, $dataset );
		}
		$content []= "</div>";
	}
}

$opd->graph->ns( "lyou", "http://purl.org/linkingyou/" );
$content []= "<h2>Linking-you</h2>";
$content []= "<p>These terms link an organisation to web-pages organistations commonly have. See <a href='http://purl.org/linkingyou/'>http://purl.org/linkingyou/</a> for more information on these terms.</p>";
$content []= $rv->html_report( "linking-you", $opd->org );

$f3->set('results', join( "", $content ) );
serve_results();


#vcard
#linkingyou
#adr
#airport
#foaf:based_naer


function serve_results()
{
        $f3=Base::instance();
	$f3->set('html_title', "Organisation Profile Document (OPD) Checker Results" );
	$f3->set('content','opd-results.html');
	$f3->set('note','' );
	print Template::instance()->render( "page-template.html" );
}

 
