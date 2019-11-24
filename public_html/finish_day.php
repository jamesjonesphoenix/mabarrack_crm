<?php

namespace Phoenix;

include '../src/crm_init.php';

$time_s = roundTime( date( 'H:i:s' ) );  //get current time

$user_id = ph_validate_number( $_SESSION['user_id'] );

//Get the previous shift ID
$previousShift = PDOWrap::instance()->getRow( 'shifts', 'worker = ' . $user_id . ' AND time_finished IS NULL ORDER BY ID DESC LIMIT 1' );
//d( $previousShift );


if ( empty( $previousShift ) ) {
    echo '<h2>Already Finished Day</h2>';
    ph_redirect( 'worker_enterjob.php', array('message' => 'finished_day') );
} else {
    $minutes = (strtotime( $time_s ) - strtotime( $previousShift['time_started'] )) / 60;

//Clock off the previous shift

    if ( PDOWrap::instance()->update( 'shifts', array(
        'time_finished' => $time_s,
        'minutes' => $minutes
    ), array('ID' => $previousShift['ID']) ) ) {
        echo '<h2>Finished Day</h2>';
        ph_redirect( 'worker_enterjob.php', array('message' => 'finished_day') );
    } else {
        echo '<h2>Failed To Finish Day</h2>';
    }
}

//ph_get_template_part( 'footer' );