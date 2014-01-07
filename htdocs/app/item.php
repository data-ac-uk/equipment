<?php
class item {

	function fragment() {
		$f3=Base::instance();

		@list( $id, $suffix ) = preg_split( '/\./', $f3->get( "PARAMS.id" ), 2 );

		if( !preg_match( '/^[a-f0-9]+$/',$id ) )
		{
			print "error- bad id.";
			return;
		}
		
		if( !isset($suffix) )
		{
			$data = json_decode( file_get_contents( $this->itemCachePath($id,"html") ), true );

			print "<h2>".$data["title"]."</h2>";
			print $data["content"];

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

		if( $suffix == "html" )
		{
			
			$path = $this->itemCachePath($id,"html");
			if(!file_exists( $path )) { $f3->error(404); }
			
			$data = json_decode( file_get_contents( $path ), true );
	
			$f3->set('html_title', $data["title"] );
			$f3->set('content','content.html');
			$f3->set('html_content', $data["content"] );
			print Template::instance()->render( "page-template.html" );
			return;
		}

		if( $suffix == "ttl" )
		{
			$path = $this->itemCachePath($id,"ttl");
			if(!file_exists( $path)) { $f3->error(404); }
			$ttl = file_get_contents( $path );
			header( "Content-type: text/turtle" );
			print $ttl;
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
