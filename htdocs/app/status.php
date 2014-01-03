<?php
class status {

	function page() {
        
		$f3=Base::instance();
		$eq = $f3->eq;
		
		$f3->set('html_title', "Status Report" );
		$f3->set('content','content.html');
		$c = array();
		$status = json_decode( file_get_contents( 'data/status-v2.json' ), true );
		$c []= "<p>The data from each organisation has been normalised into <a href='/uniquip'>Uniquip Spreadsheet Format</a> and RDF encoded as Turtle (.ttl) and using the <a href='http://openorg.ecs.soton.ac.uk/wiki/Facilities_and_Equipment'>OpenOrg</a> pattern for equipment and facilities data.</p>";
		$c []= "<table class='status'>";
		$c []= "<tr>";
		$c []= "<th rowspan=\"2\" colspan=\"2\">Organisation</th>";
		$c []= "<th colspan=\"7\">Datasets</th>";
		$c []= "</tr>";
		$c []= "<tr>";

		$c []= "<th>Raw Source</th>";
		$c []= "<th>Type</th>";
		$c []= "<th>Download*</th>";
		$c []= "<th>Records</th>";
		$c []= "<th>Timestamp</th>";
		$c []= "<th>Issues</th>";
		$c []= "<th>Compliance</th>";
		$c []= "</tr>";
		foreach( $status as $feed )
		{
						
			$c []= "<tr >";
			$c []= "<td rowspan=\"".count($feed['org_datasets'])."\"><a href='".$feed["org_url"]."'><img src='".$feed["org_logo"]."' class=\"org_logo\"/></a></td>";
			
			$c []= "<td rowspan=\"".count($feed['org_datasets'])."\"><strong><a href='".$feed["org_url"]."'>{$feed["org_name"]}</a></strong> <br/> ";
			$c []= "ID: ".$feed["org_idscheme"]."/".$feed["org_id"];
			
			$c []= "</td>";
			
		
			
			
			foreach($feed['org_datasets'] as $set){
				$c []= "<td><a href='".$set["data_uri"]."' title=\"Raw source downloaded from: {$set["data_uri"]}\">dataset</a></td>";
				$c []= "<td>".array_search($set['data_conforms'],$eq->config->conformsToMap)."</td>";

				$org_id = "{$feed["org_idscheme"]}/{$feed["org_id"]}/{$set['data_hash']}";
				$c []= "<td><a href='/org/$org_id.json'>JSON</a>, ";
				$c []= "<a href='/org/$org_id.csv'>CSV</a>, ";
				$c []= "<a href='/org/$org_id.tsv'>TSV</a>, ";
				$c []= "<a href='/org/$org_id.ttl'>RDF&nbsp;(TTL)</a></td>";
				
				$c []= "<td>".$set["crawl_records"]."</td>";
			
				$c []= "<td>".@date( "D M jS, Y\nG:i", strtotime($set["crawl_timestamp"]))."</td>";
				
				

				$c []= "<td>";
				
				$errors  = array(); 
				foreach($set['crawl_notes'] as $k=>$notes){
					foreach($notes as $note){
						$errors[] = "{$note}"; 
					}
				}
				if( sizeof( $errors ) == 0 ){
					$c []= 'None';
				}else{
					$c []= "<span class=\"issuebox\" title=\"Click to find out more\">";
					$imgmap = array('errors'=>'exclamation', 'warnings'=>'error','msgs'=>'comment');
					foreach($set['crawl_notes'] as $k=>$notes){
						if(count($notes)==0) continue;
						
						$c []= "<img src=\"/resources/images/{$imgmap[$k]}.png\" class=\"issue\" />".count($notes)." ";
						
						foreach($notes as $note){
							
						}
					}

					$c []= "</span>";
				}
				
				/*
				{
					$c []= join ("<br />",$errors );
				}
				else
				{
					$head_errors = array();
					$tail_errors = $errors; 
					$head_errors[]=array_shift( $tail_errors );
//					$head_errors[]=array_shift( $tail_errors );
					$c []= join ("<br />",$head_errors );
					$c []= "<div class='show-errors-button' id='show-all-{$set['data_hash']}' style='display:none'>";
					$c []= "<a href='#' onclick='
	jQuery(\"#errors-{$set['data_hash']}\").css(\"display\",\"block\");
	jQuery(\"#show-all-{$set['data_hash']}\").css(\"display\",\"none\");
	return false;
	'>show additional ".sizeof( $tail_errors )." issue".(sizeof( $tail_errors )>1?"s":"")."</a>";
					$c []= "</div>";
					$c []= "<div class='additional-errors' id='errors-{$set['data_hash']}' >";
					$c []= join ("<br />",$tail_errors );
					$c []= "</div>";
				}*/
				$c []= "</td>";
				
				
				$c []= "<td> <img src='/resources/images/gongs/equipment-data-{$set["crawl_gong"]}-30.png' class=\"gong\" title=\"".ucwords($set["crawl_gong"])."\"/> ".ucwords($set["crawl_gong"])."</td>";
				$c []= "</tr>";
			}	
			/*
			
			$c []= "<td>".$feed["items"]."</td>";
			$c []= "<td>".@date( "D M jS, Y\nG:i", $feed["dataset_timestamp"])."</td>";
			$c []= "<td>";
			if( sizeof( $feed["errors"] ) <= 3 )
			{
				$c []= join ("<br />",$feed["errors"]);
			}
			else
			{
				$head_errors = array();
				$tail_errors = $feed["errors"]; 
				$head_errors[]=array_shift( $tail_errors );
				$head_errors[]=array_shift( $tail_errors );
				$c []= join ("<br />",$head_errors );
				$c []= "<div class='show-errors-button' id='show-all-$org_id' style='display:none'>";
				$c []= "<a href='#' onclick='
jQuery(\"#errors-$org_id\").css(\"display\",\"block\");
jQuery(\"#show-all-$org_id\").css(\"display\",\"none\");
return false;
'>show additional ".sizeof( $tail_errors )." issue".(sizeof( $tail_errors )>1?"s":"")."</a>";
				$c []= "</div>";
				$c []= "<div class='additional-errors' id='errors-$org_id' >";
				$c []= join ("<br />",$tail_errors );
				$c []= "</div>";
			}
		
			$c []= "</td>";
				*/
			$c []= "</tr>";
			
		}
		$c []= "</table>";
		

		$c []= "<div id=\"dialog\">";
			
		$c []= "hello";
		$c []= "</div>";
		
		$c []= <<<END
<script type='text/javascript'>
jQuery( document ).ready( function() { 
	jQuery( ".additional-errors" ).css('display','none');
	jQuery( ".show-errors-button" ).css('display','block');
	
	$('#dialog').dialog();
} );
</script>";
END;
		#$c []= "<pre>".htmlspecialchars( print_r($status ,true))."</pre>";

		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	}
}
