<?php

namespace Phoenix;

include '../src/crm_init.php';

////  SETUP PAGE VARIABLES  ////
$pageTitle = ''; //page title string
$pageTableString = ''; //page table string
$pageQueryString = ''; //page query string
$pageQueryArguments = []; //page query arguments
$pageGroupColumnString = ''; //page group column string
$pageRows = []; //page rows
$pageColumns = []; //page columns
$formExcludedColumns = []; //form excluded columns
$pi = [];
////  LOAD URL PARAMETERS  ////
if ( isset( $_GET['id'] ) ) {

    $id = ph_validate_number( $_GET['id'] );

    $pi = PDOWrap::instance()->getRow( 'pages', array('ID' => $id) );
    if ( !empty( $pi ) ) {
        $pageTitle = $pi['name'];
        $pageTableString = $pi['table_name'];
        $pageQueryString = $pi['query'];
        $pageQueryArguments = explode( ',', $pi['qargs'] );
    }

} else {
    if ( isset( $_GET['t'] ) ) {
        $pageTableString = $_GET['t'];
    } //check if table given
    if ( isset( $_GET['q'] ) ) {
        $pageQueryString = $_GET['q'];
    } //check if query given
    if ( isset( $_GET['qa'] ) ) {
        $pageQueryArguments = explode( ',', $_GET['qa'] );
    } //check if query arguments given
}

if ( isset( $_GET['g'] ) ) {
    $pageGroupColumnString = $_GET['g'];
} //check if group column given
if ( isset( $_GET['fe'] ) ) {
    $formExcludedColumns = explode( ',', $_GET['fe'] );
} //check if any form excluded columns

////  IF TABLE NOT GIVEN, DISPLAY ERROR AND RETURN TO MAIN MENU  ////
if ( empty( $pageTableString ) ) {
    echo "<script>setTimeout(function() {location.href = 'index.php';},1000);</script>\n";
    exit( '<h1>ERROR - Invalid Parameters</h1><h2>Redirecting to main menu</h2>' );
}
////  GET ALL ROWS FROM TABLE  ////
if ( $pageQueryString === '' ) {
    $pageRows = PDOWrap::instance()->getRows( $pageTableString );
} else {

    if ( $pageQueryString === 'jurg' ) {
        $joburg_th = PDOWrap::instance()->getRow( 'settings', array('name' => 'joburg_th') )['value'];
        $pageQueryArguments = [$joburg_th];
    }

    $pageColumns = getColumnsQuery( $pageQueryString, $pageQueryArguments ); //get the columns of this query (works even if there are no results)
    $pageRows = getRowsQuery( $pageQueryString, $pageQueryArguments ); //get the rows of this query
    //$pageColumns = array_keys($pageRows[0]) ?? [];
}

?>
    <div class="container mt-3">
        <div class='row'>
            <div class='col-md-3'>
                <a href="index.php" class="page-header-breadcrumb">
                    <div class="btn btn-default">◀ &nbsp; Main Menu</div>
                </a></div>
            <?php
            if ($pageRows !== false) { ?>
            <div class='col-md-9'><?php
                echo generateGroupByForm( $id, $pageTableString, $pageGroupColumnString );
                //echo generate_searchform($pageTableString);
                ?></div>
        </div>
    </div><?php
    if ( array_key_exists( 'activity', $pageRows[0] ) ) {
        $activityFactory = new ActivityFactory( PDOWrap::instance(), Messages::instance() );
        $activities = $activityFactory->getActivities( [], true );
        foreach ( $pageRows as $pageKey => $pageRow ) {
            $pageRows[$pageKey]['activity'] = $activities[$pageRow['activity']]->displayName;
        }
    }
    /*display correct Minutes on shifts page*/
    if ( array_key_exists( 'time_started', $pageRows[0] ) && array_key_exists( 'time_finished', $pageRows[0] ) ) {
        foreach ( $pageRows as $pageKey => $pageRow ) {
            if ( empty( $pageRows[$pageKey]['time_finished'] ) && !empty( $pageRows[$pageKey]['time_started'] ) ) {
                $pageRows[$pageKey]['minutes'] = 'Unfinished Shift';
            } else {
                $shiftMinutes = DateTime::timeDifference( $pageRows[$pageKey]['time_started'], $pageRows[$pageKey]['time_finished'] );
                if ( $shiftMinutes < 0 ) {
                    $shiftMinutes = '<strong>Error: Finish time before start time</strong>';
                }
                $pageRows[$pageKey]['minutes'] = $shiftMinutes;
            }
        }
    }
} else {
    echo '</div>';
}
?>
    <div class="container">
        <div class='row'>
            <div class='col-md-12'><h2 class='pgtitle'><?php echo $pageTitle ?></h2>
                <?php

                if ( $_GET['id'] != 4 ) {
                    echo "<a class='btn btn-default add-button' href='" . getDetailPage( $pageTableString ) . "?add'>Add</a>";
                }
                ?>
            </div>
        </div>
    </div>
    <?php
////  IF GROUP COLUMN SET, DISPLAY ROWS IN GROUPS  ////
if ( ($pageGroupColumnString != '') and ($pageRows !== false) ) {
    $groups = []; //List of unique group values
    //Generate a list of all the unique groups
    foreach ( $pageRows as $pageRow ) {
        //Add the group if not already in the list

        if ( !in_array( $pageRow[$pageGroupColumnString], $groups ) ) {
            $groups[] = $pageRow[$pageGroupColumnString];
        }
    }
    //$groups = array_keys($pageRows[0]);
    //Go through each group and output their rows
    foreach ( $groups as $group ) {
        $groupString = '';
        if ( ($pageGroupColumnString === 'job') && ($group == 0) ) { //row job id is 0 (internal)
            $heading = 'Factory';
        } else if ( $pageGroupColumnString === 'status' ) {
            $jobStatus = PDOWrap::instance()->getRow( 'settings', array('name' => $group) )['value'];
            $groupString = $group;
            $heading = $pageGroupColumnString . ': ' . $jobStatus;
        } else {
            $heading = str_replace( '_', ' ', $pageGroupColumnString ) . ': ' . str_replace( '_', ' ', $group );
        }
        echo sprintf( '<div class="container"><div class="row%s"><div class="col-md-12"><div class="panel panel-default"><h3>%s</h3>', $groupString, $heading );
        $g_rows = []; //List of rows that belong to this group
        //Generate list of rows with this group value
        foreach ( $pageRows as $pageRow ) {
            if ( $pageRow[$pageGroupColumnString] == $group ) {
                unset( $pageRow[$pageGroupColumnString] );

                $g_rows[] = $pageRow;
            }
        }


        echo generateTable( array_keys( $g_rows[0] ), $g_rows, $pageTableString ); //Output the table for this group
        echo '</div></div></div></div>';
    }
} ////  NO GROUP column GIVEN, DISPLAY ROWS AS SINGLE TABLE  ////
else {
    ?>
    <div class="container">
        <div class="row">
            <div class='panel panel-default'>
                <?php echo generateTable( $pageColumns, $pageRows, $pageTableString ); ?>
            </div>
        </div>
    </div>
<?php } ?>

    <script>
        var page_id = <?php echo $id; ?>;
        if (page_id == 5 || page_id == 6)
            var table_sorter_options = [[1, 0], [0, 0]];
        /*
        else if( page_id == 4 )
        var table_sorter_options = [ [ 0, 0 ], [ 0, 1 ] ];
        */
        pageFunctions();
    </script>

    <?php getTemplatePart( 'footer' ); ?>