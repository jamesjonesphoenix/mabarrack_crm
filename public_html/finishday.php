<?php

namespace Phoenix;

include '../src/crm_init.php';

$time_s = roundTime( date( 'H:i:s' ) );  //get current time

$user_id = ph_validate_number( $_SESSION['user_id'] );

//Get the previous shift ID
$previousShift = PDOWrap::instance()->getRow( 'shifts', 'worker = ' . $user_id . ' AND time_finished IS NULL ORDER BY ID DESC LIMIT 1' );
if ( $previousShift !== false ) {
    $minutes = (strtotime( $time_s ) - strtotime( $previousShift['time_started'] )) / 60;

//Clock off the previous shift
    $columns = ['ID', 'time_finished', 'minutes'];
    $data = [$previousShift['ID'], $time_s, $minutes];
    $update_row = update_row( 'shifts', $columns, $data );
    if ( $update_row !== TRUE ) {
        echo $update_row;
    } else {
        echo '<h2>Logging Out</h2>';
        ph_redirect( 'worker_enterjob.php', array('message' => 'finished_day') );
    }
}

ph_get_template_part( 'footer' );