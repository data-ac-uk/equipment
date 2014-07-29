<?php
class compliance {
	
	function podium() {
		        
		$f3=Base::instance();
		$eq = $f3->eq;
				
		$c = array();
		
		$f3->set('html_title', "The Compliance Podium" );
		$f3->set('content','content.html');
		$podium = array();
		
		$status = json_decode( file_get_contents( 'data/status-v2.json' ), true );
		foreach( $status['orgs'] as $feed ){
			foreach($feed['org_datasets'] as $set){
				$podium[$set['crawl_gong']][] = $feed;
			}
		}
		
		$levels = array(
			"silver" => 2,
			"gold" => 1,
			"bronze" => 3
		);
		
		
		$cc = array("silver" => "", "gold" => "", "bronze" => "");
		foreach($levels as $k=>$v){
			foreach($podium[$k] as $feed){
				$cc[$k] .= "<img src='/org/{$feed["org_idscheme"]}/{$feed["org_id"]}.logo?size=small' class=\"org_logo\" style=\"height: 35px; margin:3px;\"/>";
			}
		}
		
		$c[] = "<table  style=\"width: 100%; \">";

		$c[] = "<tr>";
		foreach($levels as $k=>$v){
			$c[] = "<td rowspan=\"{$v}\" valign=\"bottom\" height=\"".(($v+1)*75)."\" width=\"33%\" style=\"vertical-align: bottom; text-align: center;\">{$cc[$k]}</td>";
		}	
		$c[] = "</tr>";		

		$c[] = "<tr><td rowspan=\"3\" style=\"background: #eee; border: 1px solid black; border-width: 2px 0px 0px 0px; height: 200px; text-align:center; padding:10px;\"><img src=\"/resources/images/gongs/equipment-data-gold-60.png\"/> <h3>Gold</h3></td></td>";
		$c[] = "<tr><td rowspan=\"2\" style=\"background: #eee; border: 1px solid black; border-width: 2px 0px 0px 0px;  height: 150px; text-align:center; padding:10px;\"><img src=\"/resources/images/gongs/equipment-data-silver-60.png\"/> <h3>Silver</h3></td></td>";
		$c[] = "<tr><td rowspan=\"1\" style=\"background: #eee; border: 1px solid black; border-width: 2px 0px 0px 0px; height: 100px; text-align:center; padding:10px;\"><img src=\"/resources/images/gongs/equipment-data-bronze-60.png\"/> <h3>Bronze</h3></td></td>";
		
		
		$c[] = "</table>";
		
		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	}

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
					$org = $feed;
					unset($feed['org_datasets']);
					$dataset['org'] = $feed;
					$hashash = true;
					break;
				}
			}
		}
		
		$f3->set('gt_hasdataset', $hashash);
		$f3->set('gt_dataset', $dataset);
		$f3->set('gt_org', $org);
		
		
		
		
		
        $c [] = Template::instance()->render('summary_table.html');
		
		
		$c[] = file_get_contents( 'ui/compliance.html' );
		

		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	}
}
