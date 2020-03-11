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
            if ( DateTime::validateDate( $value ) ) {
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
            $data[$key] = password_hash( $value, PASSWORD_BCRYPT, $ph_user->cryptoOptions );
            break;

        default:
            $data[$key] = $value;
            break;
    }

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
    $message['message'] = empty( $result ) ? 'Failed to update' : 'Successfully updated';

} else {
    $result = PDOWrap::instance()->add( $table, $data );
    $message['action'] = 'add';
    $message['message'] = empty( $result ) ? 'Failed to add' : 'Successfully added';

}
$message['message'] .= ' ' . trim( $table, 's' );
$message['status'] = empty( $result ) ? 'failure' : 'success';

$returnData['message'] = $message;
$returnData['redirectURL'] = '';
if ( $result ) {
    $returnData['result'] = true;
    $returnData['id'] = (int) $result;
} else {
    $returnData['result'] = false;
}
echo json_encode( $returnData );

if ( empty( $_SESSION['message'] ) || !is_array( $_SESSION['message'] ) ) {
    $_SESSION['message'] = array();
}
$_SESSION['message'][] = $message;

die();