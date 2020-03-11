<?php

namespace Phoenix;

include '../src/crm_init.php';
$redirectURL = getDetailPageHeader( 'page.php?id=6', 'Workers', 'Worker' );
if ( isset( $_GET['add'] ) ) { //add a new worker
    //Add Worker Form
    ?>
    <form id='worker_form' class='detail-form'>
        <table>
            <input type='hidden' name='type' value='staff'/>
            <tr>
                <td><b>Name: </b><input type='text' class='form-control w300' name='name' autocomplete='off' value=''/>
                </td>
            </tr>
            <tr>
                <td><b>PIN: </b><input type='text' maxlength='4' class='form-control w300' name='pin' autocomplete='off'
                                       value=''/></td>
            </tr>
            <tr>
                <td><b>Password: </b><input type='text' class='form-control w300'
                                                                       name='password' autocomplete='off' value=''/>
                </td>
            </tr>
            <tr>
                <td><b>Rate: </b><input type='number' step='0.01' min='0' class='form-control w100' name='rate'
                                        autocomplete='off' value='0'/><span class='currencyinput'></span></td>
            </tr>
        </table>
        <input type='submit' value='Add' class='btn btn-default' id='add-button'>
    </form>
    <?php
} else { //view existing worker
    $workerID = ph_validate_number( $_GET['id'] );

    $factory = new UserFactory(PDOWrap::instance(),Messages::instance());
    $worker = $factory->getUser( $workerID );


    if ( $worker->exists ) {
//Worker Details Form
        ?>
        <form id='worker_form' class='detail-form'>
            <table>
                <tr>
                    <td><b>ID: </b><input type='text' class='form-control viewinputp w300' name='ID' autocomplete='off'
                                          value='<?php echo $worker->id; ?>'/></td>
                </tr>
                <tr>
                    <td><b>Name: </b><input type='text' class='form-control viewinput w300' name='name'
                                            autocomplete='off'
                                            value='<?php echo $worker->name; ?>'/></td>
                </tr>
                <tr>
                    <td><b>Pin: </b><input type='text' maxlength='4' class='form-control viewinput w300' name='pin'
                                           autocomplete='off' value='<?php echo $worker->pin; ?>'/></td>
                </tr>
                <tr>
                    <td><b>Rate: </b><input type='number' step='0.01' min='0' class='form-control viewinput w100'
                                            name='rate'
                                            autocomplete='off' value='<?php echo $worker->rate; ?>'/><span
                                class='currencyinput'></span></td>
                </tr>
            </table>
            <input type='submit' value='Update' class='btn btn-default' id='update-button'>
        </form>


        <h3>Shifts</h3>
        <?php
        //List of Worker's Shifts
        $columns = array('ID', 'job', 'date', 'time_started', 'time_finished', 'minutes', 'activity');

        $shiftTableData = [];
        foreach ( $worker->shifts as $shift ) {
            $shiftTableData[] = [
                'ID' => $shift->id, //needed for View shift button
                'job' => $shift->job,
                'date' => $shift->date,
                'time_started' => $shift->timeStarted,
                'time_finished' => $shift->timeFinished,
                'minutes' => $shift->getShiftLength(),
                'activity' => $shift->activity->displayName
            ];
        }
        echo generateTable( $columns, $shiftTableData, 'shifts' );
    } else {
        echo 'no result';
    }
}
?>
    </div>
    <?php
getDetailPageFooter( '#worker_form', 'users', 'page.php?id=1' );