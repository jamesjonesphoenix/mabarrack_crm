<?php include 'include/crm_init.php';

////  SETUP PAGE VARIABLES  ////
$p_title = ""; //page title string
$p_table = ""; //page table string
$p_query = ""; //page query string
$p_qa = []; //page query arguments
$p_groupcolumn = ""; //page group column string
$p_rows = []; //page rows
$p_cols = []; //page columns
$f_excol = []; //form excluded columns
$pi = [];
////  LOAD URL PARAMETERS  ////
if ( isset( $_GET[ 'id' ] ) ) {

    $id = ph_validate_number( $_GET[ 'id' ] );

    $pis = get_rows( "pages", "WHERE ID = " . $id );

    if ( $pis !== FALSE ) {
        $pi = $pis[ 0 ];
        $p_title = $pi[ 'name' ];
        $p_table = $pi[ 'table_name' ];
        $p_query = $pi[ 'query' ];
        $p_qa = explode( ",", $pi[ 'qargs' ] );
    }
} else {
    if ( isset( $_GET[ 't' ] ) ) {
        $p_table = $_GET[ 't' ];
    } //check if table given
    if ( isset( $_GET[ 'q' ] ) ) {
        $p_query = $_GET[ 'q' ];
    } //check if query given
    if ( isset( $_GET[ 'qa' ] ) ) {
        $p_qa = explode( ",", $_GET[ 'qa' ] );
    } //check if query arguments given
}

if ( isset( $_GET[ 'g' ] ) ) {
    $p_groupcolumn = $_GET[ 'g' ];
} //check if group column given
if ( isset( $_GET[ 'fe' ] ) ) {
    $f_excol = explode( ",", $_GET[ 'fe' ] );
} //check if any form excluded columns

////  IF TABLE NOT GIVEN, DISPLAY ERROR AND RETURN TO MAIN MENU  ////
if ( empty( $p_table ) ) {
    echo "<script>setTimeout(function() {location.href = 'index.php';},1000);</script>\n";
    exit( "<h1>ERROR - Invalid Parameters</h1><h2>Redirecting to main menu</h2>" );
}
////  GET ALL ROWS FROM TABLE  ////
if ( $p_query == "" ) {
    $p_rows = get_rows( $p_table );
} else {

    if ( $p_query == "jurg" ) {
        $joburg_th = get_rows( "settings", "WHERE name = 'joburg_th'" )[ 0 ][ 'value' ];
        $p_qa = [ $joburg_th ];
    }
    $p_cols = get_columns_qry( $p_query, $p_qa ); //get the columns of this query (works even if there are no results)
    $p_rows = get_rows_qry( $p_query, $p_qa ); //get the rows of this query
}


echo "<div class='row'><div class='col-md-3'>";
echo '<a href="index.php" class="page-header-breadcrumb"><div class="btn btn-default">◀ &nbsp; Main Menu</div></a></div>';

if ( $p_rows !== FALSE ) {
    echo "<div class='col-md-9'>";
    echo generate_groupbyform( $id, $p_table, $p_groupcolumn );
    //echo generate_searchform($p_table);
    echo "</div></div>";
    if ( in_array( "activity", array_keys( $p_rows[ 0 ] ) ) ) {
        foreach ( $p_rows as $pkey => $p_row ) {
            $a_rows = get_rows( "activities", "WHERE ID in (" . $p_row[ 'activity' ] . ")" );
            $a_str = "";
            foreach ( $a_rows as $a_row ) {
                $a_str .= $a_row[ 'name' ];
            }
            $p_rows[ $pkey ][ 'activity' ] = $a_str;
        }
    }
    /*display correct Minutes on shifts page*/
    if ( in_array( "time_started", array_keys( $p_rows[ 0 ] ) ) && in_array( "time_finished", array_keys( $p_rows[ 0 ] ) ) ) {
        foreach ( $p_rows as $pkey => $p_row ) {
            $shift_minutes = ( strtotime( $p_rows[ $pkey ][ 'time_finished' ] ) - strtotime( $p_rows[ $pkey ][ 'time_started' ] ) ) / 60;
            if ( $shift_minutes < 0 ) $shift_minutes = '<strong>Error: Finish time before start time</strong>';
            $p_rows[ $pkey ][ 'minutes' ] = $shift_minutes;
        }
    }
} else {
    echo "</div>";
}

echo "<div class='row'><div class='col-md-12'><h2 class='pgtitle'>" . $p_title . "</h2>";


if ( $_GET[ 'id' ] != 4 ) {
    echo "<a class='btn btn-default addbtn' href='" . get_detailpage( $p_table ) . "?add'>Add</a></div></div>";
} else {
    echo "</div></div>";
}

////  IF GROUP COLUMN SET, DISPLAY ROWS IN GROUPS  ////
if ( ( $p_groupcolumn != "" ) and ( $p_rows !== FALSE ) ) {
    $groups = []; //List of unique group values
    //Generate a list of all the unique groups
    foreach ( $p_rows as $p_row ) {
        //Add the group if not already in the list
        if ( !in_array( $p_row[ $p_groupcolumn ], $groups ) ) {
            $groups[] = $p_row[ $p_groupcolumn ];
        }
    }

    //Go through each group and output their rows
    foreach ( $groups as $group ) {

        if ( ( $p_groupcolumn == "job" ) && ( $group == 0 ) ) { //row job id is 0 (internal)
            echo "<div class='row'><div class='col-md-12'><div class='panel panel-default'><h3>Factory</h3>"; //Output group value
        } else if ( $p_groupcolumn == "status" ) {
            echo "<div class='row " . $group . "'><div class='col-md-12'><div class='panel panel-default'><h3>" . $p_groupcolumn . ": " . $jstats = get_rows( "settings", "WHERE name = '" . $group . "'" )[ 0 ][ 'value' ] . "</h3>"; //Output group value
        } else {
            echo "<div class='row'><div class='col-md-12'><div class='panel panel-default'><h3>" . str_replace( "_", " ", $p_groupcolumn ) . ": " . str_replace( "_", " ", $group ) . "</h3>"; //Output group value
        }
        $g_rows = []; //List of rows that belong to this group
        //Generate list of rows with this group value
        foreach ( $p_rows as $p_row ) {
            if ( $p_row[ $p_groupcolumn ] == $group ) {
                unset( $p_row[ $p_groupcolumn ] );

                $g_rows[] = $p_row;
            }
        }


        echo generate_table( array_keys( $g_rows[ 0 ] ), $g_rows, $p_table ); //Output the table for this group
        echo "</div></div></div>";
    }
} ////  NO GROUP column GIVEN, DISPLAY ROWS AS SINGLE TABLE  ////
else {
    echo "<div class='panel panel-default'>";
    echo generate_table( $p_cols, $p_rows, $p_table );
    echo "</div>";
}
?>

    <script>
        var page_id = <?php echo $id; ?>;
        if ( page_id == 5 || page_id == 6)
            var table_sorter_options = [ [ 1, 0 ], [ 0, 0 ] ];
        /*
        else if( page_id == 4 )
        var table_sorter_options = [ [ 0, 0 ], [ 0, 1 ] ];
        */
        pagefunctions();
    </script>

<?php include 'include/footer.php'; ?>