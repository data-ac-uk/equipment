<?php
class item {

	function fragment() {
		$f3=Base::instance();

		$eq = $f3->eq;
		$eq->launch_db();
		

		@list( $id, $suffix ) = preg_split( '/\./', $f3->get( "PARAMS.id" ), 2 );

		if( !preg_match( '/^[a-f0-9]+$/',$id ) )
		{
			print "error- bad id.";
			return;
		}
		
		if( !isset($suffix) )
		{
			$page = $eq->db->fetch_one('itemPages', array('page_id' => $id));
			
			if(!$page){
				$f3->error(404);
			}
					
			print "<h2>".$page["page_title"]."</h2>";
			print $page["page_content"];
		
			print "<div class=\"feedback_enq\">If your search on our database results in an equipment or facilities collaboration we'd like to hear about it, all feedback on both successes and challenges will help us in enabling more partnerships. Click on the feedback tab.</div>";
		
			return;
		}

		print "unknown suffix";
	}

	function page()
	{
		$f3=Base::instance();
		@list( $id, $suffix ) = preg_split( '/\./', $f3->get( "PARAMS.id" ), 2 );

		if( !preg_match( '/^[a-f0-9]+$/',$id ) )
		{
			$f3->error(404,"error- bad id $id");
			return;
		}
		
		if( !isset($suffix) )
		{
			# would do clever content negotiation, but...
			# for now...
			$suffix = "html";
		}
		 
		$eq = $f3->eq;
		$eq->launch_db();
		

		if( $suffix == "html" )
		{
			$page = $eq->db->fetch_one('itemPages', array('page_id' => $id));
			
			if(!$page){
				$f3->error(404);
			}
			
			$page["page_content"] .= "<div class=\"feedback_enq\">If your search on our database results in an equipment or facilities collaboration we'd like to hear about it, all feedback on both successes and challenges will help us in enabling more partnerships. Click on the feedback tab.</div>";
		
					
			$f3->set('html_title', $page["page_title"] );
			$f3->set('content','content.html');
			$f3->set('html_content', $page["page_content"] );
			print Template::instance()->render( "page-template.html" );
			return;
		}

		if( $suffix == "ttl" )
		{
		
			$page = $eq->db->fetch_one('itemRDF', array('rdf_id' => $id));
			
			if(!$page){
				$f3->error(404);
			}
			header( "Content-type: text/turtle" );
			print $page['rdf_rdf'];
			return;
		}
		
		$f3->error(404,"unknown suffix");
		
	}
	
	function itemCachePath( $itemid, $suffix = false )
	{
		$path = "../var/item/".substr($itemid,0,2)."/".substr($itemid,2,2);
	
		$path .= "/".$itemid;
		if($suffix !== false) 
		{
			$path .= ".".$suffix;
		}
		return $path;
	}
	
}
