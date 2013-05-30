$(function() {
<?php

$lp_rows = file( '../../var/learning-providers-plus.tsv' );
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
        select: function(event,ui) { 
                $('#qs-sort').val(ui.item.value); 
                quick_search(); 
        }
    });
    $( "#qs-sort" ).keyup(function() {
        quick_search();
    });

    $('#qs-input').keyup(function() {
        quick_search();
    });

    $( "#qs-clear-sort" ).click(function() {
        $('#qs-sort').css('color','#999');
        $('#qs-sort').val( "University of...");
        quick_search();
    });

    $( "#qs-sort" ).focus(function() {
        var sort_name = $('#qs-sort').val();
        if( sort_name == "" || sort_name =="University of...") {
            $('#qs-sort').css('color','#000');
            $('#qs-sort').val( "" );
        }
    });
    $( "#qs-sort" ).blur(function() {
        var sort_name = $('#qs-sort').val();
        if( sort_name == "" || sort_name =="University of...") {
            $('#qs-sort').css('color','#999');
            $('#qs-sort').val( "University of...");
        }
    });

    $('#units-toggle').click(function() {
        if( $('#units').text() == 'km' )
        {
            $('#units').text("miles");
        }
        else
        {
            $('#units').text("km");
        }
        quick_search();
    });

    $('#qs-input').focus();

    quick_search();

    function quick_search() {
        var text = $('#qs-input').val(); 
        var sort_name = $('#qs-sort').val().toUpperCase(); 
        var sort = "";
        if( sort_name == "" || sort_name =="UNIVERSITY OF...") {
            $('#qs-sort').css('background-image','none');
            $('#qs-clear-sort').hide();
        } 
        else if( locations[sort_name] ) { 
            sort = locations[sort_name]["E"]+","+locations[sort_name]["N"];
            $('#qs-sort').css('background-image','url(/resources/images/accept.png');
            $('#qs-clear-sort').show();
        }
        else {
            $('#qs-sort').css('background-image','url(/resources/images/cancel.png');
            $('#qs-clear-sort').show();
        }
    
        if (text.length > 2) {
            $('#helpstring').hide();
            $('#sort-option').show();
            $('#results-container').show();
            $('#results').scrollTop(0);
            $.get('/search', 
                {
                   'term': text, 
                   'units': $('#units').text(),
                   'sort': sort
                }, 
                function(results) { $('#results').html( results ); },
                'html' );
        }
    
        if (text.length < 3) {
            $('#sort-option').hide();
            $('#results-container').hide();
            $('#helpstring').show();
            clear_results();
        }
    }
    
    
    function clear_results() {
        $('#featured-result').html('');
        $('#results').html('');
    }

});

// this function is called from the code produced by ajax so is tricky
// to not put in the main namespace
function show_result( id )
{
    $.get('item/'+id+".fragment", function(page) {
        $('#featured-result').scrollTop(0);
        $('#featured-result').html( page );
    }, 'html');
}
    
