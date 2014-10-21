<?php
class reports {

	function search() {
		$f3=Base::instance();
		$eq = $f3->eq;
		
		$f3->set('html_title', "Search Terms" );
		$f3->set('content','content.html');
		$c = array();
		
		$startdate = date("Y-m-d 00:00:00", strtotime("-1 month"));
		$enddate = date("Y-m-d 23:59:59");
		
		$eq->launch_db();
		$statuses = $eq->db->exec(
		"SELECT COUNT( * ) AS  `Rows` ,  `search_term` 
FROM  `statsSearchTerms`
WHERE `search_date` >= ? AND `search_date` <=  ?
GROUP BY  `search_term`
ORDER BY  `Rows` DESC",
		 array(1=>$startdate,2=>$enddate));	
		
		
		$c[] = "<h2>From: {$startdate} to: $enddate</h2>";
		
		$c[] = "<table class='status'>";
		
		$c[] = "<tr>";
			$c[] = "<th>Term</th>";
			$c[] = "<th>No of Searches</th>";
		$c[] = "</tr>";	
		
		foreach($statuses as $ser){
		$c[] = "<tr>";
			$c[] = "<td>{$ser['search_term']}</td>";
			$c[] = "<td>{$ser['Rows']}</td>";
		$c[] = "</tr>";	

		}
			
		
		
		
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