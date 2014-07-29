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


require_once( "../etc/eq_config.php" );
require_once( "{$eq_config->pwd}/dataacukEquipment.php" );
$eq = new dataacukEquipment($eq_config);

$f3->eq = $eq;

$f3->set('DEBUG',3);
$f3->set('AUTOLOAD',"app/");
$f3->set('UI','ui/');

$note = "";
if( file_exists( "ui/note.html" ) )
{
	$note = Template::instance()->render( "note.html" );
}
$f3->set( "note", $note );


$f3->route('GET /faq', 'faq->page' );
/*$f3->route('GET /faq',
	function() use($f3) {
                $f3=Base::instance();

		$f3->set('html_title', "Frequently Asked Questions" );
		$f3->set('content','faq.html');
		print Template::instance()->render( "page-template.html" );
	}
);

*/


$f3->route('GET /troubleshooting',
	function() use($f3) {
                $f3=Base::instance();

		$f3->set('html_title', "Troubleshooting" );
		$f3->set('content','troubleshooting.html');
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
        $f3->reroute('/posters');
    }
);

$f3->route('GET /posters',
	function() use($f3) {
                $f3=Base::instance();

		$f3->set('html_title', "Posters" );
		$f3->set('content','poster.html');
		print Template::instance()->render( "page-template.html" );
	}
);

$f3->route('GET /info',
	function() use($f3) {
		
		$f3=Base::instance();
		print Template::instance()->render( "useful_info.html" );
	}
);

$f3->route('GET /', 'home->page' );
$f3->route('GET /status', 'status->page' );
$f3->route('GET /compliance', 'compliance->page' );
$f3->route('GET /search', 'search->fragment' );
$f3->route('GET /data/search', 'search->data' );
$f3->route('GET	/org/@type/@id/@dataset', 'org->page' );
$f3->route('GET	/org/@type/@id.logo', 'logo->getLogo' );
$f3->route('GET	/item/@id', 'item->page' );
$f3->route('GET /item/@id.fragment', 'item->fragment' );

$f3->route('GET /org/ukprn-@id',
    function() {
        $f3=Base::instance();
		$id = $f3->get('PARAMS.id');
		$type = (substr($id,0,1)=='X') ? 'other' : 'ukprn';
		$f3->reroute("/org/$type/$id");
    }
);

$f3->route('GET /data/org/ukprn-@id',
    function() {
        $f3=Base::instance();
		$id = $f3->get('PARAMS.id');
		$type = (substr($id,0,1)=='X') ? 'other' : 'ukprn';
		$f3->reroute("/org/$type/$id");
    }
);

$f3->set('ONERROR',function() use($f3) {
 	$f3=Base::instance();

	$error = $f3->get('ERROR');
	$error_title = constant('Base::HTTP_'.$error['code']);
	
	
   	$f3->set('html_title', "{$error['code']} {$error_title}" );
	$f3->set('content','content.html');
	
	$c[] = "<h2>{$error_title}</h2>";
	
	switch($error['code']){
		case "404":
			$c[] = "<p>The requested URL {$_SERVER['REDIRECT_URL']} was not found on this server.</p>";
		break;
	}
	
	if($f3->get('DEBUG')>0){
		$c[] = "<hr/>";
		$c[] = "<p>{$error['text']}</p>";
		foreach ($error['trace'] as $frame) {
			$line='';
			if (isset($frame['file']) && 
				(empty($frame['class']) || $frame['class']!='Magic') &&
				(empty($frame['function']) || !preg_match('/^(?:(?:trigger|user)_error|__call|call_user_func)/',$frame['function']))
				) {
				
				$addr=$f3->fixslashes($frame['file']).':'.$frame['line'];
				if (isset($frame['class']))
					$line.=$frame['class'].$frame['type'];
				if (isset($frame['function'])) {
					$line.=$frame['function'];
					if (!preg_match('/{.+}/',$frame['function'])) {
						$line.='(';
						if (!empty($frame['args']))
							$line.=$f3->csv($frame['args']);
						$line.=')';
					}
				}
				$str=$addr.' '.$line;
				$c[] = '&bull; '.nl2br($f3->encode($str)).'<br />';
			}
		}
	}
	
	$f3->set('html_content',join("",$c));

	print Template::instance()->render( "page-template.html" );
	exit();
});


$f3->run();
exit;
