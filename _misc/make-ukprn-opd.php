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
$opd->ns("owl","http://www.w3.org/2002/07/owl#");
$opd->ns("dcat","http://www.w3.org/ns/dcat#");
$opd->ns("dcterms","http://purl.org/dc/terms/");
$opd->ns("oo","http://purl.org/openorg/");

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


echo $postcode = $org->get("ospost:postcode");
echo "-----\n";


$opd->addCompressedTriple( $opduri, "foaf:based_near", $postcode);

foreach($graph->t['sp']["{$postcode}"] as $p=>$v){
	$opd->addTriple( $postcode, $p, $v[0]);
}

$equrl = "http://equipment.{$org_url_d}/url.csv";
$conforms = "http://equipment.data.ac.uk/uniquip";


$opd->addCompressedTriple( $equrl, "a", "dcat:Download");
$opd->addCompressedTriple( $equrl, "oo:organization", $opduri);
$opd->addCompressedTriple( $equrl, "dcterms:subject", "http://purl.org/openorg/theme/equipment");
$opd->addCompressedTriple( $equrl, "dcterms:conformsTo", "$conforms");
$opd->addCompressedTriple( $equrl, "dcterms:license", "http://creativecommons.org/publicdomain/zero/1.0/");
$opd->addCompressedTriple( $equrl, "oo:contact", "mailto:equpiment-data@{$org_url_d}");
$opd->addCompressedTriple( $equrl, "oo:corrections", "mailto:equpiment-data@{$org_url_d}");



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