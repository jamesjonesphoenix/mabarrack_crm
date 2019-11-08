<?php
namespace Phoenix;
define( 'DOING_AJAX', true );
include '../src/crm_init.php';

$data = [];
$columns = [];

if ( empty( $_POST[ 'ID' ] ) && $_POST[ 'ID' ] !== false ) {
    unset($_POST['ID']);
}

foreach ( $_POST as $key => $d ) {
    if ( $key !== 'table') {
        if ( $key === 'name') {
            $data[] = ucwords($d);
        }
        elseif ( strpos( $key, 'date' ) !== false ) {
            if ( DateTime::validate_date( $d ) ) {
                {
                    $data[] = date('Y-m-d', strtotime($d));
                }
            }
            else if ( $key === 'date_started' ) {
                $data[] = date('Y-m-d');
            } //default today if not filled.
            else {
                $data[] = 'NULL';
            }
        } elseif ( $key === 'password' ) {
            if ( !empty( $d ) ) {
                $ph_user = new User();
                $data[] = password_hash( $d, PASSWORD_BCRYPT, $ph_user->getCryptoOptions() );
            } else {
                $data[] = '';
            }
        } else {
            $data[] = $d;
        }

        $columns[] = $key;
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
if ( ( $time_s !== 0 ) && ( $time_f !== 0 ) ) {
    $minutes = ( $time_f - $time_s ) / 60;
    $data[] = $minutes;
    $columns[] = 'minutes';
}
$table = $_POST[ 'table' ];
$message = array(
    'code' => 'add_entry',
    'table' => $table,
    'columns' => $columns,
    'data' => $data,
);
if ( isset( $_GET[ 'update' ] ) ) {
    $ar = update_row( $table, $columns, $data );
    $message[ 'action' ] = 'update';
} else {
    $ar = add_row( $table, $columns, $data );
    $message[ 'action' ] = 'add';
}

if ( $ar !== TRUE ) {
    $message[ 'message' ] = $ar;
    $message[ 'status' ] = 'failure';
    echo $ar;
} else {
    echo 'success';
    $message[ 'status' ] = 'success';
}
if ( empty( $_SESSION[ 'message' ] ) || !is_array( $_SESSION[ 'message' ] ) ) {
    $_SESSION['message'] = array();
}
$_SESSION[ 'message' ][] = $message;
die();