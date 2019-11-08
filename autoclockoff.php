<?php

namespace Phoenix;

define( 'DOING_CRON', true );
$message_prepend = 'Mabarrack CRM - CRON autoclockoff.php - ';


if ( (include __DIR__ . 'src/crm_init.php') !== true ) {
    $fail_message = $message_prepend . 'Was unable to include crm_init.php';
    trigger_error( $fail_message );
    echo $fail_message;
    exit();
}

$cronLog = new CronLogging( $message_prepend, array(
    'subject' => 'Mabarrack CRM CRON autoclockoff',
    'to' => TO_EMAIL,
    'from' => FROM_EMAIL
) );

$cronLog->add_log( 'Starting.' );
$min_finish_time = '16:30:00';
//Get the previous shift ID
if ( function_exists( 'get_rows' ) ) {
    $unfinished_shifts = get_rows( 'shifts', "WHERE time_finished IS NULL ORDER BY ID DESC" );
    if ( $unfinished_shifts !== FALSE ) { //there are unfinished shifts from the day
        $found_message = count( $unfinished_shifts ) > 1 ? count( $unfinished_shifts ) . ' unfinished shifts.' : '1 unfinished shift.';
        $cronLog->add_log( 'Found ' . $found_message );
        foreach ( $unfinished_shifts as $unfinished_shift ) {
            //Clock off the previous shift

            $clockoff_time = $unfinished_shift[ 'time_started' ] > $min_finish_time ?
                date( 'H:i:s', strtotime( $unfinished_shift[ 'time_started' ] ) + 60 ) : $min_finish_time;


            $data = [ $unfinished_shift[ 'ID' ], $clockoff_time, 0 ]; //clock off everyone at 4:30pm. Run script at 5:30pm.
            $result = update_row( 'shifts', [ 'ID', 'time_finished', 'minutes' ], $data );
            if ( $result !== TRUE ) {
                $cronLog->add_log( 'Failed to update shift - ' . $result );
                exit();
            }

            $cronLog->add_log( 'Successfully clocked off shift with ID of ' . $unfinished_shift['ID'] . '.' );
        }
    } else {
        $cronLog->add_log( 'No unfinished shifts found today.' );
    }
} else {
    $cronLog->add_log( 'get_rows() doesn\'t exist. Probably means we failed to include the functions library.' );
}
$cronLog->add_log( 'Finished' );
$cronLog->email_log();