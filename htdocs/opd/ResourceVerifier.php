<?php 

class ResourceVerifier
{
	var $config;

function __construct( $config_file )
{
	$contents = file_get_contents( $config_file ) ;
	if( !$contents ) { throw new Exception( "Could not read file '$config_file'" ); }
	$this->config = json_decode( $contents, true );
	if( !$this->config )
	{
		throw new Exception( "Error parsing config json: ".json_last_error_msg() );
	}
}

function html_report( $section_id, $resource )
{
	$graph = $resource->g;

	$section = $this->config[$section_id];

	$h = array();
	$h []= "<table class='rv_data'>";
	$h []= "<tr><td colspan='2' class='rv_uri'>".$resource->link()."</td></tr>";
	foreach( $section["terms"] as $term )
	{
		$h []= "<tr>";
		$h []= "<th>".$term["label"].":</th>";
		$h []= "<td>";
		if( $resource->has( $term["term"] ) )
		{
			foreach( $resource->all( $term["term"] ) as $value )
			{
				$fn = "render_default";
				if( @$term["render"] )
				{
					$fn = "render_".$term["render"];
				}
			
				$h []= "<div title='$value'>";
				$h []= $this->$fn( $graph, $value, $term );
				$h []= $this->verify( $graph, $value, $term );
				$h []= "</div>";
			}
		}
		else
		{
			$h []= "<span class='rv_null'>NULL</span>";
			if( @$term["recommended"] )
			{
				$h []= $this->render_problem( "This unset field is recommended" );
			}
		}
		$h []= "</td>";
		$h []= "</tr>";
	}
	$h []= "</table>";

	return join( "", $h );
}

function verify( $graph, $value, $term )
{
	if( @$term["expect"] )
	{
		if( $term["expect"] == "literal" && get_class( $value ) != "Graphite_Literal" )
		{
			return $this->render_problem( "expected a literal value" );
		}
		if( $term["expect"] == "resource" && get_class( $value ) != "Graphite_Resource" )
		{
			return $this->render_problem( "expected a resource" );
		}
	}
	if( @$term["expect_scheme"] )
	{
		$ok = false;
		list( $scheme, $rest ) = preg_split( "/:/", $value );
		foreach( $term["expect_scheme"] as $a_scheme )
		{
			if( $a_scheme == $scheme )
			{
				$ok = true;
				break;
			}
		}
		if( !$ok )
		{
			return $this->render_problem( "expected URI with scheme ".join( " or ", $term["expect_scheme"] ) );
		}
	}
}

function render_default( $graph, $value, $term )
{
	return $value;	
}

function render_problem( $msg )
{
	return " <span class='rv_message'>WARNING: $msg</span>";
}

function render_uri( $graph, $value, $term )
{
	return $graph->shrinkURI( $value );
}
	
function render_uri_values( $graph, $value, $term )
{
	$svalue = $graph->shrinkURI( $value );

	if( @$term["values"][$svalue] )
	{
		return "<em>".$term["values"][$svalue]."</em>";
	}

	return $svalue;
}

function render_img( $graph, $value, $term )
{
	return "<img src='$value' />";
}

function render_link( $graph, $value, $term )
{
	return $value->link();
}

function render_pretty_link( $graph, $value, $term )
{
	return $value->prettyLink();
}


		

}
		
// for when json_last_error_msg isn't defined
if (!function_exists('json_last_error_msg'))
{
    function json_last_error_msg()
    {
        switch (json_last_error()) {
            default:
                return;
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
        }
        throw new Exception($error);
    }
}

