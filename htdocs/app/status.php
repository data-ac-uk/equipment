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
		foreach( $status['orgs'] as $feed )
		{
			
			$c []= "<tr >";
			$c []= "<td rowspan=\"".count($feed['org_datasets'])."\"><a href='".$feed["org_url"]."'><img src='/org/{$feed["org_idscheme"]}/{$feed["org_id"]}.logo?size=small' class=\"org_logo\"/></a></td>";
			
			$c []= "<td rowspan=\"".count($feed['org_datasets'])."\"><strong><a href='".$feed["org_url"]."'>{$feed["org_name"]}</a></strong> <br/> ";
			$c []= "ID: ".$feed["org_idscheme"]."/".$feed["org_id"];
			
			$c []= "</td>";
			
		
			
			foreach($feed['org_datasets'] as $set){
				$c []= "<td><a href='".$set["data_uri"]."' title=\"Raw source downloaded from: {$set["data_uri"]}\">dataset</a></td>";
				$c []= "<td>".array_search($set['data_conforms'],$eq->config->conformsToMap)."</td>";

				$org_id = "{$feed["org_idscheme"]}/{$feed["org_id"]}/{$set['data_hash']}";
				$c []= "<td><a href='/org/$org_id'>HTML</a>, ";
				$c []= "<a href='/org/$org_id.json'>JSON</a>, ";
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
					$c []= "<span class=\"issuebox\" title=\"Click to find out more\" onclick=\"opendilog('{$set['data_hash']}');\">";
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
				
					$c []= "<div class='additional-errors' id='errors-{$set['data_hash']}' >";
					$c []= join ( $i );
					$c []= "</div>";
				
				}
				
			
				$c []= "</td>";
				
				
				$c []= "<td> <a href=\"/compliance?dataset={$set['data_hash']}#summary\"><img src='/resources/images/gongs/equipment-data-{$set["crawl_gong"]}-30.png' class=\"gong\" title=\"".ucwords($set["crawl_gong"])."\"/> ".ucwords($set["crawl_gong"])."</a></td>";
				$c []= "</tr>";
			}	
	
			$c []= "</tr>";
			
		}
		$c []= "</table>";
		

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
		#$c []= "<pre>".htmlspecialchars( print_r($status ,true))."</pre>";


		$c []= "<h3>Totals</h3>";
		$c []= "This archive contains ".number_format($status['totals']['items'],0)." items from {$status['totals']['orgs']} organisations.";

		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	}
}
