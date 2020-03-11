<?php

namespace Phoenix;

include '../src/crm_init.php';

$userID = CurrentUser::instance()->id;

//Get the previous shift
$shiftFactory = new ShiftFactory( PDOWrap::instance(), Messages::instance() );
$unfinishedShift = $shiftFactory->getWorkerUnfinishedShift( $userID );
if ( !empty( $unfinishedShift ) ) {
    $unfinishedShift->finishShift();
}

$currentTime = DateTime::roundTime( date( 'H:i:s' ) ); //get current time

$jobID = !empty($_GET['job_id']) ? ph_validate_number( $_GET['job_id'] ) : 0;
$activityID = !empty($_GET['activity_id']) ? ph_validate_number( $_GET['activity_id'] ) : 0;

$data = [
    'job' => $jobID,
    'worker' => $userID,
    'date' => date( 'Y-m-d' ),
    'time_started' => $currentTime,
    'activity' => $activityID,
    'minutes' => 0,
    'furniture' => !empty($_GET['furniture_id']) ? ph_validate_number( $_GET['furniture_id'] ) : 0,
    'activity_comments' => $_GET['comment'] ?? null
];
/*
if ( $jobID !== 0 ) {
    //$data['furniture'] = ph_validate_number( $_GET['furniture_id'] );
} elseif ( ph_validate_number( $_GET['activity_id'] ) === 14 ) { //Lunch
    $data['activity_comments'] = $_GET['comment'];
}
*/

$heading = $activityID === 0 ? 'Lunch Started!' : 'Shift Started!';

if ( PDOWrap::instance()->add( 'shifts', $data ) ) { ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container">
                <h1><?php echo $heading; ?></h1>
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
getTemplatePart( 'footer' ) ?>



