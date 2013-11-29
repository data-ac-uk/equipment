<?php

$eq_config = (object) NULL;

$eq_config->pwd = dirname(__DIR__);

$eq_config->opds = (object) NULL;
$eq_config->opds->local = "{$eq_config->pwd}/etc/opds";
$eq_config->opds->direct = array(
	array("path"=>"http://data.southampton.ac.uk/dumps/profile/2013-10-24/profile.ttl","type"=>"url"),
);

$eq_config->opds->autodiscovers = array(
	"http://data.ox.ac.uk/",
	"http://www.roslin.ed.ac.uk/"
);

$eq_config->id = (object) NULL;
$eq_config->id->overides = array(
	"http://id.myuni.ac.uk/"=>"other/X99",
	"http://www.roslin.ed.ac.uk/#org"=>"other/X1"
);



$eq_config->conformsToMap = array(
	"http://openorg.ecs.soton.ac.uk/wiki/Facilities_and_Equipment" => "rdf",
	"http://equipment.data.ac.uk/uniquip" => "uniquip"
);
