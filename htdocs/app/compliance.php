<?php
class compliance {

	function page() {
        
		$f3=Base::instance();
		$eq = $f3->eq;
		
		$f3->set('html_title', "Compliance" );
		$f3->set('content','content.html');
//		$f3->set('compliance_page','compliance.html');

		$c = array();


		$summary = array();
		$summary['data'] = array("desc" => "Data is on the internet and in an acceptable format.", "gongs"=>array(1,1,1));
		$summary['opd'] = array("desc" => "Description of dataset is provided by a remotely hosted OPD", "gongs"=>array(0,1,1));
		$summary['opd-auto'] = array("desc" => "The OPD is discovered via autodiscovery.", "gongs"=>array(0,0,1));
		$summary['licence'] = array("desc" => "The OPD/dataset has a recognised and supported open licence (eg CCO, ODCA or OGL)", "gongs"=>array(0,0,1));

	
		$f3->set('gt_gongs', array('bronze'=>'Bronze','silver'=>'Silver','gold'=>'Gold'));

		$f3->set('gt_summary', $summary);

		$hashash = false;
		if(isset($_REQUEST['dataset'])){
			$status = json_decode( file_get_contents( 'data/status-v2.json' ), true );
			foreach( $status['orgs'] as $feed ){
				if(isset($feed['org_datasets'][$_REQUEST['dataset']])){
					$dataset = $feed['org_datasets'][$_REQUEST['dataset']];
					unset($feed['org_datasets']);
					$dataset['org'] = $feed;
					$hashash = true;
					break;
				}
			}
		}
		
		$f3->set('gt_hasdataset', $hashash);
		$f3->set('gt_dataset', $dataset);
		
		
		
		
		
        $c [] = Template::instance()->render('summary_table.html');
		
		
		$c[] = file_get_contents( 'ui/compliance.html' );
		

		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	}
}
