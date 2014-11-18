<?php

$eq_config = (object) NULL;

$eq_config->pwd = dirname(__DIR__);

$eq_config->cachepath = "{$eq_config->pwd}/var/tmp";

$eq_config->host = 'equipment.data.ac.uk';
$eq_config->uribase = 'http://id.equipment.data.ac.uk/';

$eq_config->maxcahceage = 1209600; #2 weeks

$eq_config->opds = (object) NULL;
$eq_config->opds->local = "{$eq_config->pwd}/etc/opds";
$eq_config->opds->direct = array(
	array("path"=>"http://id.leeds.ac.uk/","type"=>"url"),
	array("path"=>"http://www.ncl.ac.uk/nclrdf/newcastle.ttl","type"=>"url"),
	array("path"=>"http://www.aber.ac.uk/en/media/departmental/ibers/iberskit/OPD.txt", "type"=>"url"),
	array("path"=>"https://www.ucl.ac.uk/platforms/ucl-opd/", "type"=>"url"),
	array("path"=>"http://www.kcl.ac.uk/innovation/research/corefacilities/opd/equipmentOPD.txt", "type"=>"url"),
	array("path"=>"http://eip.stfc.ac.uk/EquipData/opd_eip.ttl", "type"=>"url")
);

$eq_config->opds->autodiscovers = array(
	"http://www.roslin.ed.ac.uk/",
	"http://www.rothamsted.ac.uk/",
	"http://www.babraham.ac.uk/"
);

$eq_config->id = (object) NULL;
$eq_config->id->overides = array(
	"http://id.myuni.ac.uk/"=>"other/X99",
	"http://www.roslin.ed.ac.uk/#org"=>"other/X1",
	"http://www.rothamsted.ac.uk/#org"=>"other/X3",
	"http://www.aber.ac.uk/en/ibers/#org"=>"other/X7",
	"http://www.eip.rl.ac.uk/#org"=>"other/X8",
	"http://www.babraham.ac.uk/#org"=>"ukprn/10032038",
	"http://id.lancaster.ac.uk/"=>"ukprn/10007768",
	"http://id.sgul.ac.uk/"=>"ukprn/10007782"
);


$eq_config->conformsToMap = array(
	"rdf" => "http://openorg.ecs.soton.ac.uk/wiki/Facilities_and_Equipment",
	"uniquip"=>"http://equipment.data.ac.uk/uniquip",
	"kitcat"=>"http://equipment.data.ac.uk/kitcat-items-json",
	"rdf-n8"=>"http://equipment.n8research.org.uk/research-equipment.html",
	"pure"=>"http://equipment.data.ac.uk/pure"
);

$eq_config->licences = array(
	"ogl" => array('uri'=>"http://www.nationalarchives.gov.uk/doc/open-government-licence/", "label"=>"OGL - The (UK) Open Government License for Public Sector Information"),
	"odca" => array('uri'=>"http://opendatacommons.org/licenses/by/", "label"=>"ODCA - Open Data Commons Attribution License"),
	"cc0" => array('uri'=>"http://creativecommons.org/publicdomain/zero/1.0/", "label"=>"CC0 - Public Domain Dedication")
);

$eq_config->uniqupmap = array(
		"type"=>"Type",
		"name"=>"Name",
		"desc"=>"Description",
		"facid"=>"Related Facility ID",
		"technique"=>"Technique",
		"location"=> "Location",
		"contactname"=>"Contact Name",
		"contacttel"=>"Contact Telephone",
		"contacturl"=>"Contact URL",
		"contactemail"=>"Contact Email",
		"contact2name"=>"Secondary Contact Name",
		"contact2tel"=>"Secondary Contact Telephone",
		"contact2url"=>"Secondary Contact URL",
		"contact2email"=>"Secondary Contact Email",
		"lid"=>"ID",
		"photo"=>"Photo",
		"department"=>"Department",
		"sitelocation"=>"Site Location",
		"building"=>"Building",
		"servicelevel"=>"Service Level",
		"url"=>"Web Address"
);

$eq_config->uniqupextramap = array(
	"org_name"=>"Institution Name",
	"org_url"=>"Institution URL",
	"org_logo"=>"Institution Logo URL",
	"item_updated"=>"Datestamp",
	"loc_text"=>"Approximate Coordinates",
	"item_updated"=>"Corrections"
);

$eq_config->db = (object) NULL;
$eq_config->db->connection = "mysql:host=localhost;port=3306;dbname=equipment";
$eq_config->db->user = 'equipment';
$eq_config->db->password = 'equipment';

$eq_config->rapper = (object) NULL;
$eq_config->rapper->path = 'rapper';

$eq_config->gongs = array(1=>'bronze', 2=>'silver', 3=>'gold' );

$eq_config->crawler = (object) NULL;
$eq_config->crawler->emailto = array("andrew@debian");

$eq_config->messages = (object) NULL;
$eq_config->messages->from = "admin@data.ac.uk";

$eq_config->stats = "/Sites/access_log";

$eq_config->misc = (object) NULL;
if(file_exists("{$eq_config->pwd}/etc/eq_config.local.php")){
	include("{$eq_config->pwd}/etc/eq_config.local.php");
}



