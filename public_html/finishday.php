<?php include 'include/crm_init.php';

$time_s = roundTime( date( "H:i:s" ) );  //get current time

$user_id = ph_validate_number( $_SESSION[ 'user_id' ] );

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
    $update_row = update_row( "shifts", $clms, $data );
    if ( $update_row !== TRUE ) {
        echo $update_row;
    } else {
        echo "<h2>Logging Out</h2>";
        ph_redirect( 'w_enterjob.php', array( 'message' => 'finished_day' ) );
    }
}

include 'include/footer.php' ?>