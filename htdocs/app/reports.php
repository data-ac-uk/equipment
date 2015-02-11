<?php
class reports {

	function index() {
		$f3=Base::instance();
		$eq = $f3->eq;
		
		$f3->set('html_title', "Reports" );
		$f3->set('content','content.html');
		
		$c []= "<ul>";
		
		$c []= "<li><a href=\"/reports/crawlhistory\">Crawl History</a></li>";
		$c []= "<li><a href=\"/reports/search?start=".date("Y-m-d",strtotime("-1 month"))."&end=".date("Y-m-d")."\">Searches by Term (Last month)</a></li>";
		$c []= "<li><a href=\"/reports/search?start=".date("Y-m-d",strtotime("-1 month"))."&end=".date("Y-m-d")."&byinst\">Searches by Institution (Last month)</a></li>";
		$c []= "<li><a href=\"/reports/contacts\">Institution Contacts</a></li>";
		$c []= "<li><a href=\"/reports/joined\">Institution Joining Dates</a></li>";
				
		$c []= "</ul>";
		

	
		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
		
	}

	function joined() {
		
		$f3=Base::instance();
		$eq = $f3->eq;
		
		$f3->set('html_title', "Contributers Info" );
		$f3->set('content','content.html');
		
		
		$eq->launch_db();
		
		$orgs = $eq->db->fetch_many('orgs', array('sort:1'=>'org_sort', 'sort:2'=>'org_firstseen'));
		
		
		$c []= "<table class='status'>";
		$c []= "<tr>";
		$c []= "<th>Organisation</th>";
		$c []= "<th>ID</th>";
		$c []= "<th>Status</th>";
		$c []= "<th>Joined</th>";
		$c []= "<th>Last Crawled</th>";
		$c []= "</tr>";

		foreach( $orgs as $feed )
		{
			
			if( $feed["org_ena"] != 0 ){
				$style = "style=\"background: rgb(200,255,200);\"";
			}else{	
				$style = "style=\"background: rgb(255,200,200);\"";
			}
			
			$c []= "<tr $style>";
			$c []= "<td>{$feed["org_name"]}</td>";
			$c []= "<td>{$feed["org_idscheme"]}/{$feed["org_id"]}</td>";
			$c []= "<td>".($feed["org_ena"] ? "&#x2713;" : "&#x2717;")."</td>";
			$c []= "<td>{$feed["org_firstseen"]}</td>";
			$c []= "<td>{$feed["org_lastseen"]}</td>";
			$c []= "</tr>";
		}
		
	
	

	
	$f3->set('html_content',join("",$c));
	print Template::instance()->render( "page-template.html" );

}
	function contacts() {
		$f3=Base::instance();
		$eq = $f3->eq;
		
		$f3->set('html_title', "Contact Details" );
		$f3->set('content','content.html');
		
		$status = json_decode( file_get_contents( 'data/status-v2.json' ), true );
		$c []= "<table class='status'>";
		$c []= "<tr>";
		$c []= "<th>Organisation</th>";
		$c []= "<th>ID</th>";
		$c []= "<th>Level</th>";
		$c []= "<th>Email 1</th>";
		$c []= "<th>Email 2</th>";
		$c []= "</tr>";

		foreach( $status['orgs'] as $feed )
		{
			foreach($feed['org_datasets'] as $set){

				$c []= "<tr >";
				$c []= "<td>{$feed["org_name"]}</td>";
				$c []= "<td>{$feed["org_id"]}</td>";

				$c []= "<td>".ucwords($set["crawl_gong"])."</td>";
				
				$box = "";
				if(strtolower(substr($set["data_contact"],0,7))=="mailto:"){
					$box = "<a href=\"{$set["data_contact"]}\">".substr($set["data_contact"],7)."</a>";
				}else{
					$box = $set["data_contact"];
				}
				
				$c []= "<td>{$box}</td>";
				
				$box = "";
				if($set["data_corrections"]!=$set["data_contact"]){
					if(strtolower(substr($set["data_corrections"],0,7))=="mailto:"){
						$box = "<a href=\"{$set["data_corrections"]}\">".substr($set["data_corrections"],7)."</a>";
					}else{
						$box = $set["data_corrections"];
					}
				}
				
				$c []= "<td>{$box}</td>";
				
				$c []= "</tr>";
			}	
			
		}
		$c []= "</table>";
		
		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
		
	}
	
	function search() {
		$f3=Base::instance();
		$eq = $f3->eq;
		
		if(isset($_REQUEST['start'])){
			$startdate = date("Y-m-d H:i:s",strtotime($_REQUEST['start'],strtotime("00:00:00")));
		}else{
			$startdate = date("Y-m-d 00:00:00", strtotime("-1 month"));
		}
		
		if(isset($_REQUEST['end'])){
			$enddate = date("Y-m-d H:i:s",strtotime($_REQUEST['end'],strtotime("00:00:00")));
		}else{
			$enddate = date("Y-m-d 23:59:59");
		}
	
	
		$urlbase = "/reports/search?start=".urlencode($startdate)."&end=".urlencode($enddate);
		
		if(isset($_REQUEST['byinst'])){
			$field = 'search_owner';
			$title = "Searches by Institution";
			$ftitle = 'Institution';
			$urlbase .= "&byinst";
			$afield = 'search_term';
			$atitle = 'Term';
		}else{
			$field = 'search_term';
			$title = "Searches by search term";
			$ftitle = 'Term';
			$afield = 'search_owner';
			$atitle = 'Who';
		}
		
	
		$f3->set('html_title', $title );
		$f3->set('content','content.html');
		$c = array();
		
		$c[] = "<h2>From: {$startdate} to: $enddate</h2>";
	
	
		
		$c[] = "<table class='status'>";
	
		if(isset($_REQUEST['key'])){
			
			$c[] = "<h3>Filtered Term: {$_REQUEST['key']}</h3>";
			
			$eq->launch_db();
			$statuses = $eq->db->exec(
			"SELECT * FROM  `statsSearchTerms`
	WHERE `search_date` >= ? AND `search_date` <=  ? AND `$field` = ? ORDER BY  `search_date` ASC",
			 array(1=>$startdate,2=>$enddate,3=>$_REQUEST['key']));	
			
			
 			$c[] = "<tr>";
 				$c[] = "<th>{$atitle}</th>";
 				$c[] = "<th>IP</th>";
 				$c[] = "<th>Date</th>";
 			$c[] = "</tr>";	
	
			foreach($statuses as $ser){
			$c[] = "<tr>";
				$c[] = "<td>{$ser[$afield]}</td>";
				$c[] = "<td>{$ser['search_ip']}</td>";
				$c[] = "<td>{$ser['search_date']}</td>";
			$c[] = "</tr>";	

			}
			
		}else{
			
			$eq->launch_db();
			$statuses = $eq->db->exec(
			"SELECT COUNT( * ) AS  `Rows` ,  `$field` 
	FROM  `statsSearchTerms`
	WHERE `search_date` >= ? AND `search_date` <=  ?
	GROUP BY  `{$field}`
	ORDER BY  `Rows` DESC",
			 array(1=>$startdate,2=>$enddate));	
		
		
		
			
			$c[] = "<tr>";
				$c[] = "<th>{$ftitle}</th>";
				$c[] = "<th>No of Searches</th>";
			$c[] = "</tr>";	
		
			foreach($statuses as $ser){
			$c[] = "<tr>";
				$c[] = "<td><a href=\"{$urlbase}&key=".urlencode($ser[$field])."\">{$ser[$field]}</a></td>";
				$c[] = "<td>{$ser['Rows']}</td>";
			$c[] = "</tr>";	

			}
			
		}
		
		
		$c[] = "</table>";
	
		
		
		
		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	
	}

	function crawlhistory() {
        
		$f3=Base::instance();
		$eq = $f3->eq;
		
		$f3->set('html_title', "Crawl History" );
		$f3->set('content','content.html');
		$c = array();
		
		$startdate = strtotime("today",strtotime("-14 Days"));
		$enddate = time();
		$eq->launch_db();
		$statuses = $eq->db->fetch_many('crawls', array('crawl_timestamp' => date(">\:Y-m-d",$startdate)));

		foreach($statuses as $status){
			$stats[$status['crawl_dataset']][date("Y-m-d",strtotime($status['crawl_timestamp']))] = $status;
		}
		
		for($date = $startdate; $date < $enddate;  $date = strtotime("+1 day",$date)){
			$days[] = date("Y-m-d", $date);
			$totals[ date("Y-m-d", $date) ] = 0;
		}
		
		$c[] = "<table class='status'>";
		$c[] = "<tr>";
			$c[] = "<th>Dataset</th>";
			foreach($days as $day){
				$c[] = "<th>$day</th>";
			}
			
		$c[] = "</tr>";
		
		
		foreach($stats as $statk => $stat){
			$dataset = $eq->db->fetch_one('datasets', array('data_uri' => $statk));
			$org = $eq->db->fetch_one('orgs', array('org_uri' => $dataset['data_org']));
			$c[] = "<tr>";
				$c[] = "<td>";
				$c[] = "<img src='/org/{$org["org_idscheme"]}/{$org["org_id"]}.logo?size=small' class=\"org_logo\"/>";
				$c[] = "<br/>{$org['org_id']}</td>";
				foreach($days as $day){
					if(isset($stat[$day])){
						
						$set = $stat[$day];
			
					
						$set['crawl_notes'] = json_decode($set['crawl_notes'], true);
						
						$errors  = array(); 
						foreach($set['crawl_notes'] as $k=>$notes){
							foreach($notes as $note){
								$errors[] = "{$note}"; 
							}
						}
						if( sizeof( $errors ) == 0 ){
							$c []= "<td style=\"background: rgb(200,255,200);\">";
						
							$c []= 'None';
						}else{
							$c []= "<td style=\"background: rgb(255,200,200);\">";
						
							$c []= "<span class=\"issuebox\" title=\"Click to find out more\" onclick=\"opendilog('{$set["crawl_id"]}-{$day}');\">";
							$imgmap = array('errors'=>'exclamation', 'warnings'=>'error','msgs'=>'comment');
					
							$i = array();
					
					
							$i[] = "<h5>For Dataset:</h5>";
					
							$i[] = "<div>{$set["data_uri"]}</div>";
										
							foreach($set['crawl_notes'] as $k=>$notes){
								if(count($notes)==0) continue;
						
								$i[] = "<h5>".ucwords($k)."</h5>";
								$c []= "<img src=\"/resources/images/{$imgmap[$k]}.png\" class=\"issue\" />".count($notes)." ";

								$i[] = "<ul>";
								foreach($notes as $note){
									$i[] = "<li>{$note}</li>";
								}
								$i[] = "</ul>";
							}

							$c []= "</span>";
				
							$c []= "<div class='additional-errors' id='errors-{$set["crawl_id"]}-{$day}' >";
							$c []= join ( $i );
							$c []= "</div>";
				
						}
						
						$c []= "<br/> ({$set['crawl_records']})";
			
						$c []= "</td>";
						
						$totals[$day] += $set['crawl_records'];
						
					}else
						$c[] = "<td>-</td>";
				}
			
			$c[] = "</tr>";
			
		}
		
		$c[] = "<tr>";
			$c[] = "<th>Totals</th>";
			foreach($days as $day){
				$c[] = "<td>{$totals[$day]}</td>";
			}
			
		$c[] = "</tr>";
		
		$c[] = "</table>";

		$c []= "<div id=\"issuedialog\" title=\"Dataset Issues\">";
		$c []= "</div>";
		
		$c []= <<<END
<script type='text/javascript'>
jQuery( document ).ready( function() { 
	jQuery( ".additional-errors" ).css('display','none');
	jQuery( ".show-errors-button" ).css('display','block');
	
	$( "#issuedialog" ).dialog({
	      autoOpen: false,
		  width: '500px'
	});
} );

function opendilog( hash ){
	$( "#issuedialog" ).html( $( "#errors-" + hash ).html() );
	$( "#issuedialog" ).dialog('open');
}
</script>
END;
	
		
		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	}
	
}

?>