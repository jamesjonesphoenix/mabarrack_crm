<?php
namespace Phoenix;

include '../src/crm_init.php'; ?>
<a href="page.php?id=4&g=job" class="page-header-breadcrumb">
    <div class="btn btn-default">◀ &nbsp; Shifts</div>
</a><br>
<h2>Shift Details</h2>
<div class='panel panel-default' style='position: relative'>
    <?php

    $shiftID = ph_validate_number( $_GET['id'] );
    $shiftRow = PDOWrap::instance()->getRow( 'shifts', array('ID' => $shiftID) );

    if ( $shiftRow !== false ) {
        ?>

        <input type='button' id='editbtn' value='Edit' class='btn btn-default'/>
        <input type='button' id='cancelbtn' value='Cancel' class='btn btn-default'/>
        <form id='update_shift_form' class='detailform'>
            <table>
                <tr>
                    <td width='310'><b>ID: </b><input type='text' class='form-control viewinputp w300' name='ID'
                                                      value='<?php echo $shiftRow['ID']; ?>'/></td>
                </tr>
                <tr>
                    <td><b>Job: </b><select class='form-control viewinput w300' name='job' autocomplete='off'>\n
                            \n
                            <?php
                            $jobRows = PDOWrap::instance()->getRows( 'jobs' );
                            foreach ( $jobRows as $jobRow ) {
                                $displayText = $jobRow['ID'];
                                if ( $displayText === 0 ) {
                                    $displayText = 'Factory';
                                }
                                if ( $jobRow['ID'] == $shiftRow['job'] ) {
                                    echo '<option value="' . $jobRow['ID'] . '" selected="selected">' . $displayText . "</option>\n";
                                } else {
                                    echo '<option value="' . $jobRow['ID'] . '">' . $displayText . "</option>\n";
                                }
                            }
                            ?>
                        </select></td>
                </tr>
                <?php
                $workerRows = PDOWrap::instance()->getRows( 'users', array('type' => 'staff') );
                ?>
                <tr>
                    <td><b>Worker: </b><select class='form-control viewinput w300' name='worker' autocomplete='off'>
                            <?php
                            foreach ( $workerRows as $workerRow ) {
                                if ( $workerRow['ID'] == $shiftRow['worker'] ) {
                                    echo '<option value="' . $workerRow['ID'] . '" selected="selected">' . $workerRow['name'] . '</option>';
                                } else {
                                    echo '<option value="' . $workerRow['ID'] . '">' . $workerRow['name'] . '</option>';
                                }
                            }
                            ?>
                        </select></td>
                </tr>
                <tr>
                    <td><b>Date: </b><input type='date' class='form-control viewinput w300' name='date'
                                            value='<?php echo DateTime::validate_date( $shiftRow['date'] ); ?>'
                                            autocomplete='off'/>
                    </td>
                </tr>
                <tr>
                    <td><b>Started</b><input name='time_started' type='time'
                                             value='<?php echo date( 'H:i', strtotime( $shiftRow['time_started'] ) ); ?>'
                                             class='form-control viewinput w300' autocomplete="off"></td>
                    <td><b>Finished</b><input name='time_finished' type='time'
                                              value='<?php echo date( 'H:i', strtotime( $shiftRow['time_finished'] ) ); ?>'
                                              class='form-control viewinput w300' autocomplete="off"></select></td>
                </tr>
                <?php
                //echo "<tr><td><b>Started</b><select name='time_started' class='form-control viewinput w300' autocomplete='off'>\n";
                //echo timeDropDown($shiftRow['time_started']);
                //echo "</select></td>\n";


                //echo "<td><b>Finished</b><select name='time_finished' class='form-control viewinput w300' autocomplete='off'>\n";
                //echo timeDropDown($shiftRow['time_finished']);
                //echo "</select></td></tr>\n";

                $activityRows = PDOWrap::instance()->getRows( 'activities' );
                $activities = new Activities( PDOWrap::instance() );
                ?>

                <tr>
                    <td><b>Activity: </b><select class='form-control viewinput w300' name='activity' autocomplete='off'>
                            <?php
                            foreach ( $activityRows as $activityRow ) {
                                if ( $activityRow['ID'] == $shiftRow['activity'] ) {
                                    $selected = 'selected="selected"';
                                } else {
                                    $selected = '';
                                }
                                echo '<option value="' . $activityRow['ID'] . '" ' . $selected . '>' . $activities->getDisplayName( $activityRow['ID'] ) . '</option>';
                            }
                            ?>
                        </select></td>
                </tr>
                <?php
                if ( $shiftRow['activity'] == 14 ) {
                    ?>
                    <tr>
                        <td colspan='2'><input type='text' class='viewinputp form-control'
                                               value='"<?php echo $shiftRow['activity_comments']; ?>"'></td>
                    </tr>;
                    <?php
                }


                if (!empty( $shiftRow['job'] )){
                $jobRow = PDOWrap::instance()->getRow( 'jobs', array('ID' => $shiftRow['job']) );

                if (!empty( $jobRow['furniture'] )) {
                //d($jobRow['furniture']);
                $furnitureJSON = json_decode( $jobRow['furniture'], true );
               // d($furnitureJSON);
               // d($shiftRow['furniture']);
                foreach($furnitureJSON as $furn){
                    if(!empty($furn[$shiftRow['furniture']])){
                        $furniture = $furn[$shiftRow['furniture']];
                    }
                }
                $furniture = $furniture ?? [];
                //d($furniture);
                //$furnitureID = current( array_keys( $furniture ) ) ?? 0;
                //$furnitureQuantity = reset( $furniture );
                $furnitureQuantity = !empty($furniture) ? $furniture : '';
                //d($furnitureID);
                $furnitureName = !empty( $shiftRow['furniture'] ) ? PDOWrap::instance()->getRow( 'furniture', array('ID' => $shiftRow['furniture']) )['name'] : 'Unknown or N/A';
                d($furnitureQuantity);
                $furnitureString = $furnitureQuantity . ' ' . $furnitureName ;
                $furnitureString .= !empty($furnitureQuantity) && $furnitureQuantity > 1 ? 's' : '';
                ?>
                <tr>
                    <td><b>Furniture: </b><input type='text' class='form-control viewinputp w300'
                                                 value=' <?php echo $furnitureString; ?> ' autocomplete='off'/></td>
                    <?php
                    }
                    }
                    ?>
            </table>
            <input type='submit' value='Update' class='btn btn-default' id='updatebtn'></form>
        <?php


    } else {
        echo 'no result';
    }
    //ph_script_filename() . "?" . $_SERVER[ 'QUERY_STRING' ] /*the shift we were just on*/

    getDetailPageFooter( '#update_shift_form', 'shifts', 'page.php?id=1' );
    ?>

