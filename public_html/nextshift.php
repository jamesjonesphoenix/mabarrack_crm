<?php include 'include/crm_init.php';

$time_s = roundTime( date( "H:i:s" ) ); //get current time

$user_id = ph_validate_number( $_SESSION[ 'user_id' ]);

//Get the previous shift ID
$prshtrows = get_rows( "shifts", "WHERE worker = " . $user_id . " AND time_finished IS NULL ORDER BY ID DESC LIMIT 1" );
if ( $prshtrows !== FALSE ) {
    $prev_shift = $prshtrows[ 0 ];
    $ps_id = $prev_shift[ 'ID' ];
    $ps_times = $prev_shift[ 'time_started' ];

    $minutes = ( strtotime( $time_s ) - strtotime( $ps_times ) ) / 60;

    //Clock off the previous shift
    $clms = [ 'ID', 'time_finished', 'minutes' ];
    $data = [ $ps_id, $time_s, $minutes ];
    $ur = update_row( "shifts", $clms, $data );
    if ( $ur !== TRUE ) {
        echo $ur;
    } else {
    }
}

$clms = [ 'job', 'worker', 'date', 'time_started', 'activity', 'minutes' ];
$jid = ph_validate_number( $_GET[ 'jid' ] );
$data = [ $jid, $user_id, date( "Y-m-d" ), $time_s, $_GET[ 'aid' ], 0 ];

if ( $jid != 0 ) {
    $clms[] = 'furniture';
    $data[] = $_GET[ 'fid' ];
} else {
    $aid = ph_validate_number( $_GET[ 'aid' ] );
    if ( $aid == 14 ) {
        $clms[] = 'activity_comments';
        $data[] = $_GET[ 'comment' ];
    }
}

$ar = add_row( "shifts", $clms, $data );
if ( $ar !== TRUE ) {
    echo $ar;
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
            location.href = 'w_enterjob.php';
        }, 1000 );
    </script>
    <?php
}
include 'include/footer.php' ?>