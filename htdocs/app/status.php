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
			$c []= "<tr>";
			#$c []= "<td><a href='".$feed["org_url"]."'><img src='".$feed["org_logo"]."' /></a></td>";
			$c []= "<td><a href='/org/ukprn-".$feed["org_ukprn"]."'>view</a></td>";
			$c []= "<td>".$feed["org_ukprn"]."</td>";
			$c []= "<td>".$feed["org_name"]."</td>";
			$c []= "<td>".$feed["dataset_type"]."</td>";
			$c []= "<td><a href='".$feed["dataset_url"]."'>dataset</a></td>";
			$c []= "<td><a href='/data/org/ukprn-".$feed["org_ukprn"].".json'>JSON</a>, ";
			$c []= "<a href='/data/org/ukprn-".$feed["org_ukprn"].".csv'>CSV</a>, ";
			$c []= "<a href='/data/org/ukprn-".$feed["org_ukprn"].".tsv'>TSV</a>, ";
			$c []= "<a href='/data/org/ukprn-".$feed["org_ukprn"].".ttl'>RDF&nbsp;(TTL)</a></td>";
			$c []= "<td>".$feed["items"]."</td>";
			$c []= "<td>".@date( "D M jS, Y\nG:i", $feed["dataset_timestamp"])."</td>";
			$c []= "<td>".join ("<br />",$feed["errors"])."</td>";
			$c []= "</tr>";
		}
		$c []= "</table>";
		#$c []= "<pre>".htmlspecialchars( print_r($status ,true))."</pre>";

		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	}
}
