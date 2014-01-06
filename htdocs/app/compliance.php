<?php
class compliance {

	function page() {
        
		$f3=Base::instance();
		$eq = $f3->eq;
		
		$f3->set('html_title', "Compliance" );
		$f3->set('content','content.html');
//		$f3->set('compliance_page','compliance.html');

		$c = array();
		$c[] = file_get_contents( 'ui/compliance.html' );


		$summary = array();
		$summary['data'] = array("desc" => "Data is on equipment.data.", "gongs"=>array(1,1,1));
		$summary['opd'] = array("desc" => "Description of dataset is provided by a remotely hosted OPD", "gongs"=>array(0,1,1));
		$summary['opd-auto'] = array("desc" => "The OPD is discovered via autodiscovery.", "gongs"=>array(0,0,1));
		$summary['licence'] = array("desc" => "The OPD/dataset has a recognised and supported open licence (eg CCO,ODCA or OGL)", "gongs"=>array(0,0,1));

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
		
		if($hashash){
			$c []= "<h2><a name=\"summary\"></a>Summary for Dataset</h2>";
			
			$c []= "<p>The following table shows how the dataset (<a href=\"{$dataset['data_uri']}\">{$dataset['data_uri']}</a>) from the <a href=\"{$dataset['org']['org_url']}\">{$dataset['org']['org_name']}</a> has achieved its complience level of <strong>".ucwords($dataset['crawl_gong'])."</strong></p>";
			
		}else{
			$c []= "<h2>Summary</h2>";
		}
		
				
		
		
		$c []= "<table class='status'>";
		
		$c []= "<tr>";
		$c []= "<th></th>";

		$c []= "<th width=\"20%\">Bronze</th>";
		$c []= "<th width=\"20%\">Silver</th>";
		$c []= "<th width=\"20%\">Gold</th>";
		if($hashash)
			$c []= "<th width=\"20%\">Dataset</th>";
		
		foreach($summary as $k=>$sum){
		
			$c []= "</tr>";
			$c [] = "<td>{$sum['desc']}</td>";
		
			
			for($i=0;$i<3;$i++){
				if($sum['gongs'][$i]){
					$c [] = "<td>&#10004;</td>";
				}else{
					$c [] = "<td></td>";
				}
			}
			
			
			if($hashash){
				if($dataset['crawl_gong_json'][$k]==3){
					$c [] = "<td>&#10004;</td>";
				}else{
					$c [] = "<td></td>";
				}
			}
			$c []= "<tr>";
		
		}
		
		$c []= "<tr>";
		$c [] = "<td></td>";
		
		$c []= "<td> <img src='/resources/images/gongs/equipment-data-bronze-30.png' class=\"gong\" title=\"Bronze\"/> Bronze</td>";
		$c []= "<td> <img src='/resources/images/gongs/equipment-data-silver-30.png' class=\"gong\" title=\"Silver\"/> Silver</td>";
		$c []= "<td> <img src='/resources/images/gongs/equipment-data-gold-30.png' class=\"gong\" title=\"Gold\"/> Gold</td>";
		
		$c []= "<td> <img src='/resources/images/gongs/equipment-data-{$dataset["crawl_gong"]}-30.png' class=\"gong\" title=\"".ucwords($dataset["crawl_gong"])."\"/> ".ucwords($dataset["crawl_gong"])."</td>";
				
		$c []= "</tr>";
		
		$c []= "</table>";
		
		

		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	}
}
