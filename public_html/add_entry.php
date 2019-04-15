<?php
define( 'DOING_AJAX', true );
include 'include/crm_init.php';

$data = [];
$clms = [];

if ( empty( $_POST[ 'ID' ] ) && $_POST[ 'ID' ] !== false )
    unset( $_POST[ 'ID' ] );

foreach ( $_POST as $key => $d ) {
    if ( $key != "table" ) {
        if ( $key == "name" )
            $data[] = ucwords( $d );
        elseif ( strpos( $key, 'date' ) !== false ) {
            if ( ph_DateTime::validate_date( $d ) )
                $data[] = date( 'Y-m-d', strtotime( $d ) );
            else {
                if ( $key == 'date_started' )
                    $data[] = date( 'Y-m-d' ); //default today if not filled.
                else
                    $data[] = 'NULL';
            }
        } elseif ( $key == 'password' ) {
            if ( !empty( $d ) ) {
                $ph_user = new ph_User();
                $data[] = password_hash( $d, PASSWORD_BCRYPT, $ph_user->get_crypto_options() );
            } else
                $data[] = '';
        } else
            $data[] = $d;

        $clms[] = $key;
    }
}

$time_s = 0; //Time started
$time_f = 0; //Time finished
$minutes = 0; //Minutes

//check if time_started given
if ( isset( $_POST[ 'time_started' ] ) ) {
    $time_s = strtotime( roundTime( $_POST[ 'time_started' ] ) );
}
//check if time_finished given
if ( isset( $_POST[ 'time_finished' ] ) ) {
    $time_f = strtotime( roundTime( $_POST[ 'time_finished' ] ) );
}

//if both times given, calculate the difference
if ( ( $time_s != 0 ) && ( $time_f != 0 ) ) {
    $minutes = ( $time_f - $time_s ) / 60;
    $data[] = $minutes;
    $clms[] = "minutes";
}
$table = $_POST[ 'table' ];
$message = array(
    'code' => 'add_entry',
    'table' => $table,
    'columns' => $clms,
    'data' => $data,
);
if ( isset( $_GET[ 'update' ] ) ) {
    $ar = update_row( $table, $clms, $data );
    $message[ 'action' ] = 'update';
} else {
    $ar = add_row( $table, $clms, $data );
    $message[ 'action' ] = 'add';
}

if ( $ar !== TRUE ) {
    $message[ 'message' ] = $ar;
    $message[ 'status' ] = 'failure';
    echo $ar;
} else {
    echo "success";
    $message[ 'status' ] = 'success';
}
if ( empty( $_SESSION[ 'message' ] ) || !is_array( $_SESSION[ 'message' ] ) )
    $_SESSION[ 'message' ] = array();
$_SESSION[ 'message' ][] = $message;
die();