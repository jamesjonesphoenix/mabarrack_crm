<?php

namespace Phoenix;

use Phoenix\Entity\CurrentUser;

include '../src/crm_init.php';

$startTime = date( 'H:i:s' ); //get current time

$userID = CurrentUser::instance()->id;

//Get the previous shift ID
$previousShift = PDOWrap::instance()->getRow( 'shifts', 'worker = ' . $userID . ' AND time_finished IS NULL ORDER BY ID DESC LIMIT 1' );

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
    'worker' => $userID,
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
            <div class="panel panel-default container">
                <h1>Comments for previous shift</h1>
            </div>
        </div>
    </div>
    <script>
        setTimeout(function () {
            location.href = 'worker_enterjob.php';
        }, 1000);
    </script>

    <?php getTemplatePart( 'footer' ); ?>