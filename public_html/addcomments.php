<?php include 'include/crm_init.php';

$time_s = date( "H:i:s" ); //get current time


$user_id = ph_validate_number($_SESSION[ 'user_id' ]);
//Get the previous shift ID
$prshtrows = get_rows( "shifts", "WHERE worker = " . $user_id . " AND time_finished IS NULL ORDER BY ID DESC LIMIT 1" );
if ( $prshtrows !== FALSE ) {
    $prev_shift = $prshtrows[ 0 ];
    $ps_id = $prev_shift[ 'ID' ];
    $ps_times = $prev_shift[ 'time_started' ];

    $minutes = ( strtotime( $time_s ) - strtotime( $ps_times ) ) / 60;

//echo "minutes: " . $minutes . " <br>";
//echo $time_s;
//echo $ps_id;
//Clock off the previous shift
    $clms = [ 'ID', 'time_finished', 'minutes' ];
    $data = [ $ps_id, $time_s, $minutes ];
    $ur = update_row( "shifts", $clms, $data );
    if ( $ur !== TRUE ) {
        echo $ur;
    } else {
        echo "success";
    }
}


$clms = [ 'job', 'worker', 'date', 'time_started', 'activity' ];
$data = [ $_GET[ 'jid' ], $user_id, date( "d-m-Y" ), $time_s, $_GET[ 'aid' ] ];

$ar = add_row( "shifts", $clms, $data );
if ( $ar !== TRUE ) {
    echo $ar;
} else {
    echo "success";
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
        setTimeout( function () {
            location.href = 'w_enterjob.php';
        }, 1000 );
    </script>

<?php include 'include/footer.php' ?>