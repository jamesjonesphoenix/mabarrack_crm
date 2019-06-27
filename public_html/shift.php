<?php include 'include/crm_init.php'; ?>
<a href="page.php?id=4&g=job" class="page-header-breadcrumb">
    <div class="btn btn-default">◀ &nbsp; Shifts</div>
</a><br>
<h2>Shift Details</h2>
<div class='panel panel-default' style='position: relative'>
    <?php

    $s_id = ph_validate_number( $_GET[ 'id' ] );

    $s_rows = get_rows( "shifts", "WHERE ID = " . $s_id );
    $s_row = [];
    if ( $s_rows !== FALSE ) {
        $s_row = $s_rows[ 0 ];

        echo "<input type='button' id='editbtn' value='Edit' class='btn btn-default'/>";
        echo "<input type='button' id='cancelbtn' value='Cancel' class='btn btn-default'/>";

        //shift details
        echo "<form id='update_shift_form' class='detailform'><table>";
        echo "<tr><td width='310'><b>ID: </b><input type='text' class='form-control viewinputp w300' name='ID' value='" . $s_row[ 'ID' ] . "'/></td></tr>\n";

        $jrows = get_rows( 'jobs', "" );
        echo "<tr><td><b>Job: </b><select class='form-control viewinput w300' name='job' autocomplete='off'>\n";
        foreach ( $jrows as $jrow ) {
            $displaytxt = $jrow[ 'ID' ];
            if ( $displaytxt == 0 ) {
                $displaytxt = "Factory";
            }
            if ( $jrow[ 'ID' ] == $s_row[ 'job' ] ) {
                echo '<option value="' . $jrow[ 'ID' ] . '" selected="selected">' . $displaytxt . "</option>\n";
            } else {
                echo '<option value="' . $jrow[ 'ID' ] . '">' . $displaytxt . "</option>\n";
            }
        }
        echo "</select></td></tr>";

        $wrows = get_rows( 'users', "WHERE type = 'staff'" );
        echo "<tr><td><b>Worker: </b><select class='form-control viewinput w300' name='worker' autocomplete='off'>\n";
        foreach ( $wrows as $wrow ) {
            if ( $wrow[ 'ID' ] == $s_row[ 'worker' ] ) {
                echo '<option value="' . $wrow[ 'ID' ] . '" selected="selected">' . $wrow[ 'name' ] . "</option>\n";
            } else {
                echo '<option value="' . $wrow[ 'ID' ] . '">' . $wrow[ 'name' ] . "</option>\n";
            }
        }
        echo "</select></td></tr>";
        //$time_started =
        ?>
        <tr>
            <td><b>Date: </b><input type='date' class='form-control viewinput w300' name='date'
                                    value='<?php echo ph_DateTime::validate_date( $s_row[ 'date' ] ); ?>' autocomplete='off'/></td>
        </tr>
        <tr>
            <td><b>Started</b><input name='time_started' type='time'
                                     value='<?php echo date( "H:i", strtotime( $s_row[ 'time_started' ] ) ); ?>'
                                     class='form-control viewinput w300' autocomplete="off"></td>
            <td><b>Finished</b><input name='time_finished' type='time'
                                      value='<?php echo date( "H:i", strtotime( $s_row[ 'time_finished' ] ) ); ?>'
                                      class='form-control viewinput w300' autocomplete="off"></select></td>
        </tr>
        <?php
        //echo "<tr><td><b>Started</b><select name='time_started' class='form-control viewinput w300' autocomplete='off'>\n";
        //echo timedd($s_row['time_started']);
        //echo "</select></td>\n";


        //echo "<td><b>Finished</b><select name='time_finished' class='form-control viewinput w300' autocomplete='off'>\n";
        //echo timedd($s_row['time_finished']);
        //echo "</select></td></tr>\n";


        $arows = get_rows( "activities", "" );

        echo "<tr><td><b>Activity: </b><select class='form-control viewinput w300' name='activity' autocomplete='off'>\n";
        foreach ( $arows as $arow ) {
            if ( $arow[ 'ID' ] == $s_row[ 'activity' ] ) {
                echo '<option value="' . $arow[ 'ID' ] . '" selected="selected">' . $arow[ 'name' ] . "</option>\n";
            } else {
                echo '<option value="' . $arow[ 'ID' ] . '">' . $arow[ 'name' ] . "</option>\n";
            }
        }
        echo "</select></td></tr>";

        if ( $s_row[ 'activity' ] == 14 ) {
            echo "<tr><td colspan='2'><input type='text' class='viewinputp form-control' value='" . $s_row[ 'activity_comments' ] . "'></td></tr>\n";
        }


        $jr = get_rows( 'jobs', "WHERE ID = " . $s_row[ 'job' ] )[ 0 ];

        if ( $jr[ 'furniture' ] != "" ) {
            $fjson = json_decode( $jr[ 'furniture' ], true );

            $fur = $fjson[ $s_row[ 'furniture' ] ];

            $ffid = current( array_keys( $fur ) );
            $ffq = reset( $fur );
            $ffn = get_rows( 'furniture', "WHERE ID = " . $ffid )[ 0 ][ 'name' ];
            echo "<tr><td><b>Furniture: </b><input type='text' class='form-control viewinputp w300' value='" . $ffq . " " . $ffn . ( $ffq > 1 ? "s" : "" ) . "' autocomplete='off'/></td>\n";

        }
        echo "</table><input type='submit' value='Update' class='btn btn-default' id='updatebtn'>";
        echo "</form>";


    } else {
        echo "no result";
    }
    //ph_script_filename() . "?" . $_SERVER[ 'QUERY_STRING' ] /*the shift we were just on*/

    getdetailpagefooter( "#update_shift_form", "shifts", 'page.php?id=1' );
    ?>

