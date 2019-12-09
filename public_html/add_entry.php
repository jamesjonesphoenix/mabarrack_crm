<?php

namespace Phoenix;
define( 'DOING_AJAX', true );
include '../src/crm_init.php';

$data = [];
$queryArgs = [];
//if ( !empty( $_POST['ID'] ) && $_POST['ID'] !== false ) {
  //  unset( $_POST['ID'] );
//}

foreach ( $_POST as $key => $value ) {

    switch( $key ) {
        case 'table':
            break;
        case 'ID':
            if ( isset( $_GET['update'] ) ) {
                $queryArgs = array('ID' => $value);
            } else {
                $data[$key] = $value;
            }
            break;
        case 'name':
            $data[$key] = ucwords( $value );
            break;
        case 'date':
        case 'date_started':
        case 'date_finished':
            if ( DateTime::validate_date( $value ) ) {
                {
                    $data[$key] = date( 'Y-m-d', strtotime( $value ) );
                }
            } else if ( $key === 'date_started' ) {
                $data[$key] = date( 'Y-m-d' );
            } //default today if not filled.
            else {
                $data[$key] = null;
            }
            break;
        case 'password':
            $ph_user = new User( PDOWrap::instance(), Messages::instance() );
            $data[$key] = password_hash( $value, PASSWORD_BCRYPT, $ph_user->cryptoOptions() );
            break;

        default:
            $data[$key] = $value;
            break;
    }


}

$time_s = 0; //Time started
$time_f = 0; //Time finished
$minutes = 0; //Minutes

//check if time_started given
if ( isset( $_POST['time_started'] ) ) {
    $time_s = strtotime( roundTime( $_POST['time_started'] ) );
}
//check if time_finished given
if ( isset( $_POST['time_finished'] ) ) {
    $time_f = strtotime( roundTime( $_POST['time_finished'] ) );
}

//if both times given, calculate the difference
if ( ($time_s !== 0) && ($time_f !== 0) ) {
    $minutes = ($time_f - $time_s) / 60;
    $data['minutes'] = $minutes;
}
$table = $_POST['table'];
$message = array(
    'code' => 'add_entry',
    'table' => $table,
    'data' => $data,
);
if ( isset( $_GET['update'] ) ) {
    $result = PDOWrap::instance()->update( $table, $data, $queryArgs );
    $message['action'] = 'update';

    if ( empty( $result ) ) {
        echo 'Failed to update ' . trim($table,'s');
    } else {
        echo 'Successfully updated ' . trim($table,'s');
    }
} else {


    $result = PDOWrap::instance()->add( $table, $data );

    $message['action'] = 'add';
    if ( empty( $result ) ) {
        echo 'Failed to add ' . trim($table,'s');
    } else {
        echo 'Successfully added ' . trim($table,'s');
    }
}
if ( empty( $result ) ) {
    $message['status'] = 'failure';
} else {
    $message['status'] = 'success';
}

if ( empty( $_SESSION['message'] ) || !is_array( $_SESSION['message'] ) ) {
    $_SESSION['message'] = array();
}
$_SESSION['message'][] = $message;

die();