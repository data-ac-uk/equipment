<?php
class status {

	function page() {
                $f3=Base::instance();

		$f3->set('html_title', "Status Report" );
		$f3->set('content','content.html');
		$c = array();
		$status = json_decode( file_get_contents( 'data/status.json' ), true );
		$c []= "<p>The data from each organisation has been normalised into <a href='/uniquip'>Uniquip Spreadsheet Format</a> and RDF encoded as Turtle (.ttl) and using the <a href='http://openorg.ecs.soton.ac.uk/wiki/Facilities_and_Equipment'>OpenOrg</a> pattern for equipment and facilities data.</p>";
		$c []= "<table class='status'>";
		$c []= "<tr>";
		$c []= "<th></th>";
		$c []= "<th>UKPRN</th>";
		$c []= "<th>Organisation</th>";
		$c []= "<th>Type</th>";
		$c []= "<th>Source</th>";
		$c []= "<th>Download</th>";
		$c []= "<th>Records</th>";
		$c []= "<th>Timestamp</th>";
		$c []= "<th>Issues</th>";
		$c []= "</tr>";
		foreach( $status as $feed )
		{
			$org_id = "ukprn-".$feed["org_ukprn"];
			$c []= "<tr>";
			#$c []= "<td><a href='".$feed["org_url"]."'><img src='".$feed["org_logo"]."' /></a></td>";
			$c []= "<td><a href='/org/$org_id'>view</a></td>";
			$c []= "<td>".$feed["org_ukprn"]."</td>";
			$c []= "<td>".$feed["org_name"]."</td>";
			$c []= "<td>".$feed["dataset_type"]."</td>";
			$c []= "<td><a href='".$feed["dataset_url"]."'>dataset</a></td>";
			$c []= "<td><a href='/data/org/$org_id.json'>JSON</a>, ";
			$c []= "<a href='/data/org/$org_id.csv'>CSV</a>, ";
			$c []= "<a href='/data/org/$org_id.tsv'>TSV</a>, ";
			$c []= "<a href='/data/org/$org_id.ttl'>RDF&nbsp;(TTL)</a></td>";
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
			$c []= "</tr>";
		}
		$c []= "</table>";
		$c []= "<script type='text/javascript'>
jQuery( document ).ready( function() { 
	jQuery( \".additional-errors\" ).css('display','none');
	jQuery( \".show-errors-button\" ).css('display','block');
} );
</script>";
		#$c []= "<pre>".htmlspecialchars( print_r($status ,true))."</pre>";

		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	}
}
