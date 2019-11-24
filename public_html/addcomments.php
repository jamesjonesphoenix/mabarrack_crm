<?php

namespace Phoenix;

include '../src/crm_init.php';

$startTime = date( 'H:i:s' ); //get current time


$user_id = ph_validate_number( $_SESSION['user_id'] );
//Get the previous shift ID
$previousShift = PDOWrap::instance()->getRow( 'shifts', 'worker = ' . $user_id . ' AND time_finished IS NULL ORDER BY ID DESC LIMIT 1' );

if ( $previousShift !== false ) {
    $ps_times = $previousShift['time_started'];

    $minutes = (strtotime( $startTime ) - strtotime( $ps_times )) / 60;

//echo "minutes: " . $minutes . " <br>";
//echo $startTime;
//echo $ps_id;
//Clock off the previous shift
    if (
    PDOWrap::instance()->update( 'shifts', array('time_finished' => $startTime, 'minutes' => $minutes),
        array('ID' => $previousShift['ID'])
    ) ) {
        echo 'Success';
    }else{
        echo 'Failed';
    }
}


if ( PDOWrap::instance()->add( 'shifts', array(
    'job' => $_GET['jid'],
    'worker' => $user_id,
    'date' => date( 'd-m-Y' ),
    'time_started' => $startTime,
    'activity' => $_GET['aid']
) ) ) {
    echo 'success';
} else {
    echo 'failed';
}

?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container actsbtns">
                <h1>Comments for previous shift</h1>
            </div>
        </div>
    </div>
    <script>
        setTimeout(function () {
            location.href = 'worker_enterjob.php';
        }, 1000);
    </script>

    <?php ph_get_template_part( 'footer' ); ?>