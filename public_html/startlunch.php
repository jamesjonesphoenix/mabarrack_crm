<?php

namespace Phoenix;

include '../src/crm_init.php';

$startTime = roundTime( date( 'H:i:s' ) );  //get current time

$userID = ph_validate_number( $_SESSION['user_id'] );

//Get the previous shift ID
$previousShift = PDOWrap::instance()->getRow( 'shifts', 'worker = ' . $userID . ' AND time_finished IS NULL ORDER BY ID DESC LIMIT 1' );


if ( $previousShift !== false ) {

    $minutes = (strtotime( $startTime ) - strtotime( $previousShift['time_started'] )) / 60;

    //Clock off the previous shift

    PDOWrap::instance()->update( 'shifts', array(
        'time_finished' => $startTime,
        'minutes' => $minutes
    ),
        array('ID' => $previousShift['ID']) );
}
if ( PDOWrap::instance()->add( 'shifts', array(
    'job' => 0,
    'worker' => $userID,
    'date' => date( 'Y-m-d' ),
    'time_started' => $startTime,
    'activity' => 0,
    'minutes' => 0
) ) ) {
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container actsbtns">
                <h1>Lunch Started!</h1>
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