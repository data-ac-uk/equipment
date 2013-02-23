<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html lang="en">
<!--<![endif]-->
<head>

<!-- Basic Page Needs
  ================================================== -->
<meta charset="utf-8">
<title>UK University Facilities and Equipment Data</title>
<meta value="description" content="equipment.data.ac.uk: Open data about equipment and facilities of UK Universities ">
<meta value="author" content="Christopher Gutteridge">

<!-- Mobile Specific Metas
  ================================================== -->
<meta value="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

<!-- CSS
  ================================================== -->
<link rel="stylesheet" href="http://data.ac.uk/stylesheets/base.css">
<link rel="stylesheet" href="http://data.ac.uk/stylesheets/skeleton.css">
<link rel="stylesheet" href="http://data.ac.uk/stylesheets/layout.css">
<!--<link rel="stylesheet" href="http://data.ac.uk/stylesheets/site.css">-->
<!--
<script src='/resources/jquery.min.js' ></script>
<script src="/resources/jquery-ui.js"></script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
-->
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js"></script>
<link rel="stylesheet" href="/resources/subsite-styles.css">
<link rel="stylesheet" href="/resources/site.css">


<!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

<!-- Favicons
    ================================================== -->
<link rel="shortcut icon" href="http://equipment.data.ac.uk/favicon.ico">
<link rel="apple-touch-icon" href="http://data.ac.uk/images/apple-touch-icon.png">
<link rel="apple-touch-icon" sizes="72x72" href="http://data.ac.uk/images/apple-touch-icon-72x72.png">
<link rel="apple-touch-icon" sizes="114x114" href="http://data.ac.uk/images/apple-touch-icon-114x114.png">

</head>
<body>

<!-- Primary Page Layout
    ================================================== --> 

<div class="container">
  <div id="content">
  <div class="sixteen columns padding_top_30 ">
  <h1>UK Universities Facilities &amp; Equipment Open Data</h1>
<p>This site aggregates open data about research facilities and major items of equipment from UK Universities.</p>
<h2>Quick Search</h2>

    <input id='qs-input' type='text' style='text-align:left;width:95%;font-size:140%;padding:0.1em;border:solid 1px #000;margin-bottom:3px' />
    <div id='helpstring'>Enter 3 or more characters to begin search</div>

<div class="ui-widget" id='sort-option'>
<label style='float: none;display:inline;' for="qs-sort">Sort by distance from: </label>
<input size='70' id="qs-sort" style='padding-left:20px;display:inline; border:solid 1px #000;
background: top left no-repeat; ' /><img src='/resources/icons/delete.png' style='cursor:pointer' id='qs-clear-sort'/>
</div>

</div>

<div id="results" class='eight columns'>
</div>
<div id='featured-result' class='eight columns'>
</div>

<script>
$(function() {
<?php

$lp_rows = file( "../var/learning-providers-plus.tsv" );
$title_row = array_shift( $lp_rows );
$fields = preg_split( "/\t/", chop($title_row) );
$data = array();
foreach( $lp_rows as $row )
{
    $cells = preg_split( "/\t/", chop($row) );
    $r = array();
    
    for( $i=0; $i<sizeof($fields); ++$i )
    {
        $r[$fields[$i]] = $cells[$i];
    }
    if( $r["EASTING"] == "" ) {
        # we don't have data for Northern Ireland universities
        continue;
    }
    $data[$r["PROVIDER_NAME"]] = array( "E"=> $r["EASTING"],"N"=> $r["NORTHING"] );
}
print "var locations = ".json_encode( $data ).";";
print "var availableTags = ".json_encode( array_keys( $data ) ).";";
?>
    $( "#qs-sort" ).autocomplete({
        source: availableTags,
        select: function(event,ui) { quick_search(); }
    });
    $( "#qs-sort" ).keyup(function() {
        quick_search();
    });

    $('#qs-input').keyup(function() {
        quick_search();
    });

    $( "#qs-clear-sort" ).click(function() {
        $('#qs-sort').val(''); 
        quick_search();
    });

    $('#qs-input').focus();

    quick_search();

    function quick_search() {
        var text = $('#qs-input').val(); 
        var sort_name = $('#qs-sort').val().toUpperCase(); 
        var sort = "";
        if( sort_name == "" ) {
            $('#qs-sort').css('background-image','none');
            $('#qs-clear-sort').hide();
        } 
        else if( locations[sort_name] ) { 
            sort = locations[sort_name]["E"]+","+locations[sort_name]["N"];
            $('#qs-sort').css('background-image','url(/resources/icons/accept.png');
            $('#qs-clear-sort').show();
        }
        else {
            $('#qs-sort').css('background-image','url(/resources/icons/cancel.png');
            $('#qs-clear-sort').show();
        }
    
        if (text.length > 2) {
            $('#helpstring').hide();
            $('#sort-option').show();
            $.post('search.php', {'term': text, 'sort': sort}, function(results) {
            $('#results').html( results ); } , 'html');
        }
    
        if (text.length < 3) {
            $('#sort-option').hide();
            $('#helpstring').show();
            clear_results();
        }
    }
    
    
    function clear_results() {
        $('#featureed-result').html('');
        $('#results').html('');
    }

});
// this function is called from the code produced by ajax so is tricky
// to not put in the main namespace
    function show_result( id )
    {
        $.get('item.php', {'id': id}, function(page) {
            $('#featured-result').html( page );
        }, 'html');
    }
    
</script>
</div>
</div>
 </body>
</html>
<script type="text/javascript" src="//network-bar.data.ac.uk/network-bar.js"></script>


