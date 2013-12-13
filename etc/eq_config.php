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
	"rdf" => "http://openorg.ecs.soton.ac.uk/wiki/Facilities_and_Equipment",
	"uniquip"=>"http://equipment.data.ac.uk/uniquip",
	"kitcat"=>"http://equipment.data.ac.uk/kitcat-items-json"
);

$eq_config->licences = array(
	"ogl" => array('uri'=>"http://www.nationalarchives.gov.uk/doc/open-government-licence/", "label"=>"OGL - The (UK) Open Government License for Public Sector Information"),
	"odca" => array('uri'=>"http://opendatacommons.org/licenses/by/", "label"=>"ODCA - Open Data Commons Attribution License"),
	"cc0" => array('uri'=>"http://creativecommons.org/publicdomain/zero/1.0/", "label"=>"CC0 - Public Domain Dedication")
);


$eq_config->db = (object) NULL;
$eq_config->db->connection = "mysql:host=localhost;port=3306;dbname=equipment";
$eq_config->db->user = 'equipment';
$eq_config->db->password = 'equipment';
