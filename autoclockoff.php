<?php

namespace Phoenix;

define( 'DOING_CRON', true );
include __DIR__ . '/src/crm_init.php';
if ( !defined( 'SYSTEM_TITLE' ) ) { //check if crm_init included
    trigger_error( 'Was unable to include crm_init.php', E_USER_ERROR );
    exit();
}

$messages = Messages::instance();
$messages->emailArgs = array(
    'prepend' => SYSTEM_TITLE . ' CRM - CRON autoclockoff - ',
    'subject' => SYSTEM_TITLE . ' CRM - CRON autoclockoff',
    'to' => TO_EMAIL,
    'from' => FROM_EMAIL
);

$messages->add( 'Starting.' );
$minFinishTime = '16:30:00';
//Get the previous shift ID
$unfinishedShifts = PDOWrap::instance()->getRows( 'shifts', array('time_finished' => null) );

if ( !empty( $unfinishedShifts ) ) { //there are unfinished shifts from the day
    $found_message = 'Found ' . count( $unfinishedShifts ) . ' unfinished shift';
    $found_message .= count( $unfinishedShifts ) > 1 ? 's.' : '.';
    $messages->add( $found_message );

    foreach ( $unfinishedShifts as $unfinishedShift ) {
        //Clock off the previous shift
        $clockOffTime = $unfinishedShift['time_started'] > $minFinishTime ?
            date( 'H:i:s', strtotime( $unfinishedShift['time_started'] ) + 60 ) : $minFinishTime;


        if ( !PDOWrap::instance()->update( 'shifts',
            array('time_finished' => $clockOffTime, 'minutes' => 0),
            array('ID' => $unfinishedShift['ID']) ) ) {
            $messages->add( 'Failed to update shift.' );
        } else {
            $messages->add( 'Successfully clocked off shift <span class="badge badge-primary">ID: ' . $unfinishedShift['ID'] . '</span>' );
        }
    }
} else {
    $messages->add( 'No unfinished shifts found today.' );
}
$messages->add( 'Finished' );
$messages->email();