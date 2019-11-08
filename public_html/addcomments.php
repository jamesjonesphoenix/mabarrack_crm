<?php
namespace Phoenix;

include '../src/crm_init.php';

$time_s = date('H:i:s'); //get current time


$user_id = ph_validate_number($_SESSION['user_id']);
//Get the previous shift ID
$previousShift = PDOWrap::instance()->getRow( 'shifts','worker = ' . $user_id . ' AND time_finished IS NULL ORDER BY ID DESC LIMIT 1' );

if ($previousShift !== false) {
    $ps_times = $previousShift['time_started'];

    $minutes = (strtotime($time_s) - strtotime($ps_times)) / 60;

//echo "minutes: " . $minutes . " <br>";
//echo $time_s;
//echo $ps_id;
//Clock off the previous shift
    $data = [$previousShift['ID'], $time_s, $minutes];
    if (update_row('shifts', array('ID', 'time_finished', 'minutes'), $data) !== true) {
        echo $ur;
    }
    else {
        echo 'success';
    }
}


$data = [$_GET['jid'], $user_id, date('d-m-Y'), $time_s, $_GET['aid']];

$ar = add_row('shifts', array('job', 'worker', 'date', 'time_started', 'activity'), $data);
if ($ar !== TRUE) {
    echo $ar;
} else {
    echo 'success';
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

<?php ph_get_template_part('footer'); ?>