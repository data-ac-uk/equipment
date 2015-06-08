#!/usr/bin/php
<?php

# change these to your arc2 & graphite locations
require_once( "../lib/arc2/ARC2.php" );
require_once( "../lib/Graphite/Graphite.php" );

$STDERR = fopen('php://stderr', 'w+');

$ukprn = $argv[1];
if((int)($ukprn) == $ukprn){ 
	$ukprn = "http://learning-provider.data.ac.uk/ukprn/{$ukprn}.ttl";
}

fwrite($STDERR,"loading: $ukprn\n");

$graph = new Graphite();
$graph->load( $ukprn );
$graph->ns("ospost","http://data.ordnancesurvey.co.uk/ontology/postcode/");

reset($graph->t['sp']);
$uri = key($graph->t['sp']);

$org = $graph->resource( $uri );
$org_name = myCase($org->get( "rdfs:label" ));

$opduri =  $org->get( "foaf:homepage" )."#org";
$opduri_a = $org->get( "foaf:homepage" )."#address";
$org_homepage = (string)$org->get( "foaf:homepage");



$org_url = parse_url($org->getString( "foaf:homepage" ));

require 'tldextract.php';
$org_url_c = tldextract($org_url['host']);
$org_url_d = $org_url_c->domain.".".$org_url_c->tld;

$opd = new Graphite();

$opd->ns("org","http://www.w3.org/ns/org#");
$opd->ns("vcard","http://www.w3.org/2006/vcard/ns#");
$opd->ns("ospost","http://data.ordnancesurvey.co.uk/ontology/postcode/");
$opd->ns("spatialrelations","http://data.ordnancesurvey.co.uk/ontology/spatialrelations/");
$opd->ns("xsd","http://www.w3.org/2001/XMLSchema#");
$opd->ns("owl","http://www.w3.org/2002/07/owl#");
$opd->ns("dcat","http://www.w3.org/ns/dcat#");
$opd->ns("dcterms","http://purl.org/dc/terms/");
$opd->ns("oo","http://purl.org/openorg/");
$opd->ns("lyou","http://purl.org/linkingyou/");

$opd->addCompressedTriple( "profile.ttl", "a", "oo:OrganizationProfileDocument");
$opd->addCompressedTriple( "profile.ttl", "http://xmlns.com/foaf/0.1/primaryTopic", $opduri);

$opd->addCompressedTriple( $opduri, "a", "org:FormalOrganization");

$opd->addCompressedTriple( $opduri, "skos:prefLabel", $org_name);
$opd->addCompressedTriple( $opduri, "rdfs:label", $org_name);
$opd->addCompressedTriple( $opduri, "foaf:logo", $org->get( "foaf:homepage" )."logo.png");
$opd->addCompressedTriple( $opduri, "foaf:homepage", $org->get( "foaf:homepage"));
$opd->addCompressedTriple( $opduri, "foaf:mbox", "mailto:equiries@{$org_url_d}");
$opd->addCompressedTriple( $opduri, "foaf:phone", "tel:+441234567890");

$opd->addCompressedTriple( $opduri, "owl:sameAs","http://id.learning-provider.data.ac.uk/ukprn/{$argv[1]}");


$opd->addCompressedTriple( $opduri, "vcard:adr", $opduri_a);

foreach($graph->t['sp']["{$uri}#address"] as $p=>$v){
	@$opd->addTriple( $opduri_a, $p, $v[0]);
}

$sameas = @$org->all( "http://www.w3.org/2002/07/owl#sameAs" );	
$uris = array();
foreach($sameas as $same){
	$uria = parse_url($same);
	$uria['uri'] = (string)$same;
	$uris[$uria['host']] = $uria;
	$opd->addCompressedTriple( $opduri, "owl:sameAs",(string)$same);
	
}

$postcode = $org->get("ospost:postcode");
$opd->addCompressedTriple( $opduri, "foaf:based_near", $postcode);

foreach($graph->t['sp']["{$postcode}"] as $p=>$v){
	$opd->addTriple( $postcode, $p, $v[0]);
}



//Social media,
$accccc = preg_replace("/[^A-Za-z0-9]/","",$org_url_c->domain);
$opd->addCompressedTriple( $opduri, "foaf:account", "https://twitter.com/{$accccc}");
$opd->addCompressedTriple( "https://twitter.com/{$accccc}",  "a", "foaf:OnlineAccount");
$opd->addCompressedTriple( "https://twitter.com/{$accccc}", "foaf:accountName", "$accccc");
$opd->addCompressedTriple( "https://twitter.com/{$accccc}", "foaf:accountServiceHomepage", "https://twitter.com/");

$opd->addCompressedTriple( $opduri, "foaf:account", "https://www.facebook.com/{$accccc}");

$opd->addCompressedTriple( "https://www.facebook.com/{$accccc}", "a", "foaf:OnlineAccount");
$opd->addCompressedTriple( "https://www.facebook.com/{$accccc}", "foaf:accountName", "$accccc");
$opd->addCompressedTriple( "https://www.facebook.com/{$accccc}", "foaf:accountServiceHomepage", "https://www.facebook.com/");

//Linking You
$opd->addCompressedTriple( $opduri, "lyou:about", "{$org_homepage}about/");
$opd->addCompressedTriple( $opduri, "lyou:events", "{$org_homepage}events/");
$opd->addCompressedTriple( $opduri, "lyou:news", "{$org_homepage}news/");



$equrl = "http://equipment.{$org_url_d}/url.csv";
$conforms = "http://equipment.data.ac.uk/uniquip";


$opd->addCompressedTriple( $equrl, "a", "dcat:Download");
$opd->addCompressedTriple( $equrl, "oo:organization", $opduri);
$opd->addCompressedTriple( $equrl, "dcterms:subject", "http://purl.org/openorg/theme/equipment");
$opd->addCompressedTriple( $equrl, "dcterms:conformsTo", "$conforms");
$opd->addCompressedTriple( $equrl, "dcterms:license", "http://creativecommons.org/publicdomain/zero/1.0/");
$opd->addCompressedTriple( $equrl, "oo:contact", "mailto:equpiment-data@{$org_url_d}");
$opd->addCompressedTriple( $equrl, "oo:corrections", "mailto:equpiment-data@{$org_url_d}");


$oaiurl = "http://archive.{$org_url_d}/cgi/oai2";
$conforms = "http://www.openarchives.org/OAI/openarchivesprotocol.html";

$opd->addCompressedTriple( $oaiurl, "a", "dcat:Download");
$opd->addCompressedTriple( $oaiurl, "oo:organization", $opduri);
$opd->addCompressedTriple( $oaiurl, "dcterms:subject", "http://purl.org/openorg/theme/ResearchOutputs");
$opd->addCompressedTriple( $oaiurl, "dcterms:conformsTo", "$conforms");
$opd->addCompressedTriple( $oaiurl, "dcterms:license", "http://creativecommons.org/publicdomain/zero/1.0/");
$opd->addCompressedTriple( $oaiurl, "oo:contact", "mailto:research@{$org_url_d}");
$opd->addCompressedTriple( $oaiurl, "oo:corrections", "mailto:research@{$org_url_d}");





$ttl = $opd->serialize('Turtle');

$replace = array(
	"/".preg_replace("/([\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|])/", '\\\$0',"<$equrl>")."(.+)/" => "#Equipment Dataset: http://opd.data.ac.uk/docs/datasets \n#<URL of dataset>\n\\0",
	"/".preg_replace("/([\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|])/", '\\\$0',"<$oaiurl>")."(.+)/" => "#Reaserch Outputs Dataset: http://opd.data.ac.uk/docs/datasets \n#<URL of oai endpoint>\n\\0",
	"/dcterms:license(.+)/" => "\\0 # Licence for the Dataset, Please pick one of the three listed on http://opd.data.ac.uk/docs/datasets",
	"/oo:contact(.+)/" => "\\0  # contact for the dataset (will be used as default contact for any records without a contact)",
	"/oo:corrections(.+)/" => "\\0 # a contact for any corrections ",
	
	"/foaf:logo(.+)/" => "\\0 # URL to University logo",
	"/foaf:mbox(.+)/" => "\\0 # Gereneric Email account (delete line if you havn't got one)",
	"/foaf:phone(.+)/" => "\\0 # Switchboard phone number",
	
	"/lyou:about(.+)/" => "\\0 # Linking-You links to key pages (More-info:http://opd.data.ac.uk/docs/key-pages)",
	"/foaf:account (.+)/" => "\\0 # Social media accounts, you also need to change the sections further down (More-info:http://opd.data.ac.uk/docs/social)"
	
 );
$ttl = preg_replace(array_keys($replace),array_values($replace),$ttl);

fwrite($STDERR,"\n\n---New OPD----------\n");
echo <<<END
# This is an example Organisation Profile Document, OPD created for {$org_name} 
# This is an RDF Turtle document and for a getting started/primer guide check out http://en.wikipedia.org/wiki/Turtle_(syntax) and http://www.w3.org/2007/02/turtle/primer/
# For documentation specificly on the OPD goto http://opd.data.ac.uk/
# To test your OPD use our online checker at http://opd.data.ac.uk/checker


END;

echo $ttl;
fwrite($STDERR,"\n\n---End OPD----------\n");

function myCase($title){
	$title = strtolower($title);
	$title = ucwords($title);
	$rep = array(" Of "=>" of ");
	$title = str_replace(array_keys($rep), array_values($rep), $title);
	return $title;
}

function graphCopy( $g1, $g2, $item )
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