<?php

namespace Phoenix;

define( 'DOING_AJAX', true );

include '../src/crm_init.php';

$userID = ph_validate_number( $_SESSION['user_id'] );

//Get the previous shift ID
$shiftFactory = new ShiftFactory( PDOWrap::instance(), Messages::instance() );
$unfinishedShift = $shiftFactory->getWorkerUnfinishedShift( $userID );
if ( empty( $unfinishedShift ) ) {
    // echo '<h2>Already Finished Day</h2>';
    ph_redirect( 'worker_enterjob', array('message' => 'finished_day') );
}

if ( $unfinishedShift->finishShift() ) {
    ph_redirect( 'worker_enterjob', array('message' => 'finished_day') );
} else {
    echo '<h2>Failed To Finish Day</h2>';
}