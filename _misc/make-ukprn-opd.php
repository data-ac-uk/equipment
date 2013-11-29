#!/usr/bin/php
<?php

# change these to your arc2 & graphite locations
require_once( "../lib/arc2/ARC2.php" );
require_once( "../lib/Graphite/Graphite.php" );


$ukprn = $argv[1];
if((int)($ukprn) == $ukprn){ 
	$ukprn = "http://learning-provider.data.ac.uk/ukprn/{$ukprn}.ttl";
}

echo "loading: $ukprn\n";

$graph = new Graphite();
$graph->load( $ukprn );
$graph->ns("ospost","http://data.ordnancesurvey.co.uk/ontology/postcode/");
echo $graph->serialize('Turtle');

reset($graph->t['sp']);
$uri = key($graph->t['sp']);

$org = $graph->resource( $uri );
$org_name = myCase($org->get( "rdfs:label" ));

$opduri =  $org->get( "foaf:homepage" )."#org";
$opduri_a = $org->get( "foaf:homepage" )."#address";

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

$opd->addCompressedTriple( "profile.ttl", "a", "oo:OrganizationProfileDocument");
$opd->addCompressedTriple( "profile.ttl", "http://xmlns.com/foaf/0.1/primaryTopic", $opduri);

$opd->addCompressedTriple( $opduri, "a", "org:FormalOrganization");

$opd->addCompressedTriple( $opduri, "skos:prefLabel", $org_name);
$opd->addCompressedTriple( $opduri, "rdfs:label", $org_name);
$opd->addCompressedTriple( $opduri, "foaf:logo", $org->get( "foaf:homepage" )."logo.png");
$opd->addCompressedTriple( $opduri, "foaf:homepage", $org->get( "foaf:homepage"));
$opd->addCompressedTriple( $opduri, "foaf:mbox", "mailto:equiries@{$org_url_d}");
$opd->addCompressedTriple( $opduri, "foaf:phone", "tel:+441234567890");







$opd->addCompressedTriple( $opduri, "vcard:adr", $opduri_a);

foreach($graph->t['sp']["{$uri}#address"] as $p=>$v){
	$opd->addTriple( $opduri_a, $p, $v[0]);
}


echo $postcode = $org->get("ospost:postcode");
echo "-----\n";


$opd->addCompressedTriple( $opduri, "foaf:based_near", $postcode);

foreach($graph->t['sp']["{$postcode}"] as $p=>$v){
	$opd->addTriple( $postcode, $p, $v[0]);
}



echo "\n\nNew OPD\n";
echo $opd->serialize('Turtle');
echo "\nENDOPD\n";

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