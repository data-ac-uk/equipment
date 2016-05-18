<?php
class newsletters {

	var $newsletters = array(
		"issue10"=>array("title"=>"Issue 10, May 2016"),
		"issue9"=>array("title"=>"Issue 9, October 2015"),
		"issue8"=>array("title"=>"Issue 8, July 2015"),
		"issue7"=>array("title"=>"Issue 7, April 2015"),
		"issue6"=>array("title"=>"Issue 6, December 2014"),
		"issue5"=>array("title"=>"Issue 5, October 2014"),
		"issue4"=>array("title"=>"Issue 4, July 2014"),
		"issue3"=>array("title"=>"Issue 3, February 2014"),
		"issue2"=>array("title"=>"Issue 2, November 2013"),
		"issue1"=>array("title"=>"Issue 1, July 2013")
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
		$article = $f3->get( "PARAMS.article" );
		if(strlen($article)){
			$path = "resources/newsletters/{$issue}/$article.html";
		}else{
			$path = "resources/newsletters/{$issue}/newsletter.html";
		}
		
		
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
		
		
		
		$content .= "<br/><a href=\"http://communicatoremail.com/IN/DCF/gIi0ExYiiGVToIeP0L10yN/\" target=\"_blank\">Subscribe to our quarterly newsletter</a>";
		$content .= "</div>";
			

		$content .= "<div class='twelve columns' >	";	
		$content .= "<h2><a href=\"/newsletters/{$issue}\">{$is['title']}</a></h2>";
		$content .= "<div class=\"newsletter\" style=\"\">";

		if(strlen($article)){
			
			$content .= "<div class=\"article\" style=\"\">";		
			$content .= file_get_contents($path);
			
			$content .= "<div class=\"clear\" style=\"\"></div>";	
			$content .= "</div>";
		

		}else{
			$content .= "<div class=\"banner\">";
			$content .= "<img src=\"/resources/newsletters/equipmentdata_banner_01.jpg\">";
			$content .= "<img src=\"/resources/newsletters/equipmentdata_banner_02.jpg\">";
			$content .= "</div>";
		
			$content .= file_get_contents($path);

			$content .= "<div class=\"banner\">";
			$content .= "<img src=\"/resources/newsletters/banner.jpg\">";
			$content .= "</div>";
		}
		
		$content .= "</div>";
		$content .= "</div>";

		$content .= "</div>";
				
		$f3->set('html_title', "Newsletters: {$is['title']}");
		$f3->set('content','content.html');
		$f3->set('html_content', $content );
		print Template::instance()->render( "page-template.html" );
	}
	
}
