<?php

namespace Phoenix;

include '../src/crm_init.php';

$time_s = roundTime( date('H:i:s') ); //get current time

$user_id = ph_validate_number( $_SESSION[ 'user_id' ]);

//Get the previous shift ID
$previousShift = PDOWrap::instance()->getRow( 'shifts', 'worker = ' . $user_id . ' AND time_finished IS NULL ORDER BY ID DESC LIMIT 1' );

if ( $previousShift !== false ) {
    $ps_id = $previousShift[ 'ID' ];
    $ps_times = $previousShift[ 'time_started' ];

    $minutes = ( strtotime( $time_s ) - strtotime( $ps_times ) ) / 60;

    //Clock off the previous shift
    $columns = [ 'ID', 'time_finished', 'minutes' ];
    $data = [ $ps_id, $time_s, $minutes ];
    $ur = update_row( 'shifts', $columns, $data );
    if ( $ur !== TRUE ) {
        echo $ur;
    } else {
    }
}

$columns = [ 'job', 'worker', 'date', 'time_started', 'activity', 'minutes' ];
$job_id = ph_validate_number( $_GET[ 'job_id' ] );
$data = [ $job_id, $user_id, date('Y-m-d'), $time_s, $_GET[ 'activity_id' ], 0 ];

if ( $job_id != 0 ) {
    $columns[] = 'furniture';
    $data[] = $_GET[ 'furniture_id' ];
} else {
    $activity_id = ph_validate_number( $_GET[ 'activity_id' ] );
    if ( $activity_id === 14 ) {
        $columns[] = 'activity_comments';
        $data[] = $_GET[ 'comment' ];
    }
}

$addRow = add_row( 'shifts', $columns, $data );
if ( $addRow !== TRUE ) {
    echo $addRow;
} else {
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container actsbtns">
                <h1>Shift Started!</h1>
            </div>
        </div>
    </div>
    <script>
        setTimeout( function () {
            location.href = 'worker_enterjob.php';
        }, 1000 );
    </script>
    <?php
}
ph_get_template_part('footer') ?>