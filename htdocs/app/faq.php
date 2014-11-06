<?php
class faq {

	function page() {
        
		$f3=Base::instance();
		$eq = $f3->eq;
		
		$f3->set('html_title', "Frequently Asked Questions" );
		$f3->set('content','content.html');
		
		
		$c = array();
		$faqs = json_decode( file_get_contents( 'examples/faq.json' ), true );
		
		$c []= "<h2>Questions</h2>";
		
		$c []= "<ol class=\"faq\">";
		foreach($faqs as $faqk=>$faq){
			$c []= "<li><a href=\"#{$faqk}\">".htmlspecialchars($faq['question'])."</a></li>";
		}
		
		$c []= "</ol>";
		
		
		$count = 0;
		
		foreach($faqs as $faqk=>$faq){
		
			$c []= "<hr/>";
			
			$count++;
			$c []= "<div class=\"answer\">";

			$c []= "<a name=\"{$faqk}\"></a>";
			if(isset($faq['oldkey'])){
				$c []= "<a name=\"{$faq['oldkey']}\"></a>";
			}
			$c []= "<h2>{$count}. ".htmlspecialchars($faq['question'])."</h2>";
			
			$text = $faq['answer'];
			
			$text = preg_replace_callback("/\[\[code=(.+)\]\]/",array($this, 'code'),$text);
			
			$c []= htmlspecialchars($text);
			$c []="</div>";
		}
		
		$f3->set('html_content',join("",$c));
		print Template::instance()->render( "page-template.html" );
	}
	
	function code($matches){
		//Have to double entite it because of f3
		$code = htmlentities( file_get_contents($matches[1]) );
		return "{$matches[1]}<div class=\"code\"> {$code} </div>";
	}
}