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
			$data = json_decode( file_get_contents( "../var/item/$id" ), true );

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
			print "error- bad id $id";
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
			$data = json_decode( file_get_contents( "../var/item/$id" ), true );
	
			$f3->set('html_title', $data["title"] );
			$f3->set('content','content.html');
			$f3->set('html_content', $data["content"] );
			print Template::instance()->render( "page-template.html" );
			return;
		}

		if( $suffix == "ttl" )
		{
			$ttl = file_get_contents( "../var/item/$id.ttl" );
			header( "Content-type: text/turtle" );
			print $ttl;
			return;
		}

		print "unknown suffix";
	}
}
