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
    $shiftFactory = new ShiftFactory( PDOWrap::instance(), Messages::instance() );
    $shift = $shiftFactory->getShift( $shiftID );
    if ( $shift->exists ) {
        $jobFactory = new JobFactory( PDOWrap::instance(), Messages::instance() );
        $jobs = $jobFactory->getAll();

        $userFactory = new UserFactory( PDOWrap::instance(), Messages::instance() );
        $workers = $userFactory->getUsers( ['type' => 'staff'] );

        $activityFactory = new ActivityFactory( PDOWrap::instance(), Messages::instance() );
        $activities = $activityFactory->getActivities( [], true );

        ?>

        <input type='button' id='edit-button' value='Edit' class='btn btn-default'/>
        <input type='button' id='cancel-button' value='Cancel' class='btn btn-default'/>
        <form id='update_shift_form' class='detailform'>
            <table>
                <tr>
                    <td width='310'><b>ID: </b><input type='text' class='form-control viewinputp w300' name='ID'
                                                      value='<?php echo $shift->id; ?>'/></td>
                </tr>
                <tr>
                    <td><b>Job: </b><select class='form-control viewinput w300' name='job' autocomplete='off'>
                            <?php
                            foreach ( $jobs as $job ) {
                                $displayText = $job->id === 0 ? 'Factory' : $job->id;
                                $selected = $job->id === $shift->job->id ? ' selected="selected"' : '';
                                echo '<option value="' . $job->id . '"' . $selected . '>' . $displayText . '</option>';
                            }

                            ?>
                        </select></td>
                </tr>
                <tr>
                    <td><b>Worker: </b>
                        <?php
                        $options = [];
                        foreach ( $workers as $worker ) {
                            $options[] = ['value' => $worker->id, 'display' => $worker->name];
                        }
                        ph_generateOptionSelect( $options, 'worker', $shift->worker->id );
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><b>Date: </b><input type='date' class='form-control viewinput w300' name='date'
                                            value='<?php echo DateTime::validateDate( $shift->date ); ?>'
                                            autocomplete='off'/>
                    </td>
                </tr>
                <tr>
                    <td><b>Started</b><input name='time_started' type='time'
                                             value='<?php echo date( 'H:i', strtotime( $shift->timeStarted ) ); ?>'
                                             class='form-control viewinput w300' autocomplete="off"></td>
                    <td><b>Finished</b><input name='time_finished' type='time'
                                              value='<?php echo date( 'H:i', strtotime( $shift->timeFinished ) ); ?>'
                                              class='form-control viewinput w300' autocomplete="off"></select></td>
                </tr>
                <?php
                //echo "<tr><td><b>Started</b><select name='time_started' class='form-control viewinput w300' autocomplete='off'>\n";
                //echo timeDropDown($shift->timeStarted);
                //echo "</select></td>\n";


                //echo "<td><b>Finished</b><select name='time_finished' class='form-control viewinput w300' autocomplete='off'>\n";
                //echo timeDropDown($shift->timeFinished );
                //echo "</select></td></tr>\n";
                ?>
                <tr>
                    <td><b>Activity: </b>
                        <?php
                        $options = [];
                        foreach ( $activities as $activity ) {
                            $options[] = ['value' => $activity->id, 'display' => $activity->displayName];
                        }
                        ph_generateOptionSelect( $options, 'activity', $shift->activity->id );
                        ?>
                    </td>
                </tr>
                <?php
                if ( $shift->activity->name === 'Other' || $shift->activity === 14 ) {
                    ?>
                    <tr>
                        <td colspan='2'><input type='text' class='viewinputp form-control'
                                               value='"<?php echo $shift->activityComments; ?>"'></td>
                    </tr>;
                    <?php
                }
                $furnitureString = $shift->getFurnitureString();
                ?>
                <tr>
                    <td><b>Furniture: </b><input type='text' class='form-control viewinputp w300'
                                                 value='<?php echo $furnitureString; ?>'
                                                 autocomplete='off'/>
                    </td>
                </tr>
            </table>
            <input type='submit' value='Update' class='btn btn-default' id='update-button'></form>
        <?php


    } else {
        echo 'no result';
    }
    //ph_script_filename() . "?" . $_SERVER[ 'QUERY_STRING' ] /*the shift we were just on*/
    ?>
</div>
<?php
    getDetailPageFooter( '#update_shift_form', 'shifts', 'page.php?id=1' );