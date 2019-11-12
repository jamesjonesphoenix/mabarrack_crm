<?php

namespace Phoenix;

define( 'DOING_CRON', true );
include __DIR__ . '/src/crm_init.php';
if ( !defined( 'SYSTEM_TITLE' ) ) { //check if crm_init included
    trigger_error( 'Was unable to include crm_init.php', E_USER_ERROR );
    exit();
}

$messages = Messages::instance();
$messages->emailArgs( array(
    'prepend' => SYSTEM_TITLE . ' CRM - CRON autoclockoff - ',
    'subject' => SYSTEM_TITLE . ' CRM - CRON autoclockoff',
    'to' => TO_EMAIL,
    'from' => FROM_EMAIL
) );

$messages->add( 'Starting.' );
$minFinishTime = '16:30:00';
//Get the previous shift ID
$unfinishedShifts = PDOWrap::instance()->getRows( 'shifts', array('time_finished' => null) );

if ( !empty($unfinishedShifts) ) { //there are unfinished shifts from the day
    $found_message = 'Found ' . count( $unfinishedShifts ) . ' unfinished shift';
    $found_message .= count( $unfinishedShifts ) > 1 ? 's.' : '.';
    $messages->add( $found_message );
    
    foreach ( $unfinishedShifts as $unfinishedShift ) {
        //Clock off the previous shift
        $clockOffTime = $unfinishedShift['time_started'] > $minFinishTime ?
            date( 'H:i:s', strtotime( $unfinishedShift['time_started'] ) + 60 ) : $minFinishTime;

        $data = [$unfinishedShift['ID'], $clockOffTime, 0]; //clock off everyone at 4:30pm. Run script at 5:30pm.
        $result = update_row( 'shifts', ['ID', 'time_finished', 'minutes'], $data );
        if ( $result !== TRUE ) {
            $messages->add( 'Failed to update shift - ' . $result );
            exit();
        }

        $messages->add( 'Successfully clocked off shift with ID of ' . $unfinishedShift['ID'] . '.' );
    }
} else {
    $messages->add( 'No unfinished shifts found today.' );
}
if ( !$messages->email() ) {
    $messages->add( 'Failed to email results.' );
}
$messages->add( 'Finished' );