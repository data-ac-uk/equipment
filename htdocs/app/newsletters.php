<?php
class newsletters {

	var $newsletters = array(
		//"issue2"=>array("title"=>"Issue 2, November 2013"),
		"issue1"=>array("title"=>"Issue 1, June 2013")
	);
	
	function index(){
		$f3=Base::instance();
		
		reset($this->newsletters );
		$first_key = key($this->newsletters);
		
		$f3->reroute("/newsletters/{$first_key}");
		exit();
	}
	
	function issue()
	{
		$f3=Base::instance();
		
		$issue = $f3->get( "PARAMS.issue" );
	
		$path = "resources/newsletters/{$issue}/newsletter.html";
		
		if(!file_exists($path)){
			$f3->error(404);
		}
		$is = $this->newsletters[$issue];
			
		$content = "<div class='container'>";       

		$content .= "<div class='four columns'><h3>Previous Issues:</h3>";
		$content .= "<ul>";
		foreach($this->newsletters as $nk => $nv){
			$content .= "<li><a href=\"/newsletters/$nk\">{$nv['title']}</a></li>";
		}
			
		$content .= "</ul>";
		
		
		$content .= "</div>";
			

		$content .= "<div class='twelve columns' >	";	
		$content .= "<h2>{$is['title']}</h2>";
		$content .= "<div class=\"newsletter\" style=\"\">";
		$content .= "<div class=\"banner\">";
		$content .= "<img src=\"/resources/newsletters/equipmentdata_banner_01.jpg\">";
		$content .= "<img src=\"/resources/newsletters/equipmentdata_banner_02.jpg\">";
		$content .= "</div>";
		$content .= file_get_contents($path);

		$content .= "<div class=\"banner\">";
		$content .= "<img src=\"/resources/newsletters/banner.jpg\">";
		$content .= "</div>";
		$content .= "</div>";
		$content .= "</div>";

		$content .= "</div>";
				
		$f3->set('html_title', "Newsletters");
		$f3->set('content','content.html');
		$f3->set('html_content', $content );
		print Template::instance()->render( "page-template.html" );
	}
	
}
