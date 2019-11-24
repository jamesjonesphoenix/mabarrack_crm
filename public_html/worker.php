<?php

namespace Phoenix;

include '../src/crm_init.php';
$redirecturl = getDetailPageHeader( 'page.php?id=6', 'Workers', 'Worker' );
if ( isset( $_GET['add'] ) ) { //add a new worker
    //Add Worker Form
    ?>
    <form id='worker_form' class='detailform'>
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
                <td><b>Password: </b><br>Workers do not require a password, however it's recommended you enter one as it
                    will be used to increase session encryption.<input type='text' class='form-control w300'
                                                                       name='password' autocomplete='off' value=''/>
                </td>
            </tr>
            <tr>
                <td><b>Rate: </b><input type='number' step='0.01' min='0' class='form-control w100' name='rate'
                                        autocomplete='off' value='0'/><span class='currencyinput'></span></td>
            </tr>
        </table>
        <input type='submit' value='Add' class='btn btn-default' id='addbtn'>
    </form>
    <?php
} else { //view existing worker
    $workerID = ph_validate_number( $_GET['id'] );

    $workerRow = PDOWrap::instance()->getRow( 'users', array('ID' => $workerID) );

    if ( $workerRow !== false ) {
//Worker Details Form
        ?>
        <form id='worker_form' class='detailform'>
            <table>
                <tr>
                    <td><b>ID: </b><input type='text' class='form-control viewinputp w300' name='ID' autocomplete='off'
                                          value='<?php echo $workerRow['ID']; ?>'/></td>
                </tr>
                <tr>
                    <td><b>Name: </b><input type='text' class='form-control viewinput w300' name='name'
                                            autocomplete='off'
                                            value='<?php echo $workerRow['name']; ?>'/></td>
                </tr>
                <tr>
                    <td><b>Pin: </b><input type='text' maxlength='4' class='form-control viewinput w300' name='pin'
                                           autocomplete='off' value='<?php echo $workerRow['pin']; ?>'/></td>
                </tr>
                <tr>
                    <td><b>Rate: </b><input type='number' step='0.01' min='0' class='form-control viewinput w100'
                                            name='rate'
                                            autocomplete='off' value='<?php echo $workerRow['rate']; ?>'/><span
                                class='currencyinput'></span></td>
                </tr>
            </table>
            <input type='submit' value='Update' class='btn btn-default' id='updatebtn'>
        </form>


        <h3>Shifts</h3>
        <?php
        //List of Worker's Shifts


        $shiftRows = PDOWrap::instance()->run( 'SELECT shifts.ID, shifts.job, shifts.worker, shifts.date, shifts.time_started, shifts.time_finished, shifts.minutes, shifts.activity, users.name as worker FROM shifts INNER JOIN users ON shifts.worker=users.ID WHERE worker = ?', [$workerID] )->fetchAll();

        if ( !empty( $shiftRows ) ) {
            $activities = new Activities( PDOWrap::instance() );
            foreach ( $shiftRows as $shiftKey => $shiftRow ) {
                $shiftRows[$shiftKey]['activity'] = $activities->getName( $shiftRow['activity'] );
            }
        }

        $columns = array('ID', 'job', 'date', 'time_started', 'time_finished', 'minutes', 'activity');
        echo generateTable( $columns, $shiftRows, 'shifts' );
    } else {
        echo 'no result';
    }
}
getDetailPageFooter( '#worker_form', 'users', 'page.php?id=1' );