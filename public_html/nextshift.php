<?php

namespace Phoenix;

include '../src/crm_init.php';

$time_s = roundTime( date( 'H:i:s' ) ); //get current time

$user_id = ph_validate_number( $_SESSION['user_id'] );

//Get the previous shift ID
$previousShift = PDOWrap::instance()->getRow( 'shifts', 'worker = ' . $user_id . ' AND time_finished IS NULL ORDER BY ID DESC LIMIT 1' );

if ( $previousShift !== false ) {

    $minutes = (strtotime( $time_s ) - strtotime( $previousShift['time_started'] )) / 60;

    //Clock off the previous shift
    PDOWrap::instance()->update( 'shifts',
        array('time_finished' => $time_s, 'minutes' => $minutes),
        array('ID' => $previousShift['ID'])
    );
}

$columns = ['job', 'worker', 'date', 'time_started', 'activity', 'minutes'];
$job_id = ph_validate_number( $_GET['job_id'] );

$data = [
    'job' => $job_id,
    'worker' => $user_id,
    'date' => date( 'Y-m-d' ),
    'time_started' => $time_s,
    'activity' => $_GET['activity_id'],
    'minutes' => 0
];
if ( $job_id !== 0 ) {
    $data['furniture'] = $_GET['furniture_id'];
} elseif ( ph_validate_number( $_GET['activity_id'] ) === 14 ) { //Lunch
    $data['activity_comments'] = $_GET['comment'];
}


if ( PDOWrap::instance()->add( 'shifts', $data ) ) { ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container actsbtns">
                <h1>Shift Started!</h1>
            </div>
        </div>
    </div>
    <script>
        setTimeout(function () {
            location.href = 'worker_enterjob.php';
        }, 1000);
    </script>
    <?php
}
ph_get_template_part( 'footer' ) ?>