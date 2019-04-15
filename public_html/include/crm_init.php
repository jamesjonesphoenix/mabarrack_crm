<?php
include dirname(__FILE__) . '../../../config.php';

//default database definitions
if ( !defined( 'DB_HOST' ) ) define( 'DB_HOST', 'localhost' );
if ( !defined( 'DB_USER' ) ) define( 'DB_USER', 'root' );
if ( !defined( 'DB_PASSWORD' ) ) define( 'DB_PASSWORD', '' );
if ( !defined( 'DB_NAME' ) ) define( 'DB_NAME', 'mabdb' );
if ( !defined( 'DB_PORT' ) ) define( 'DB_PORT', '3306' );
//default parameters
if ( !defined( 'USING_SSL' ) ) define( 'USING_SSL', false );
if ( !defined( 'SYSTEM_TITLE' ) ) define( 'SYSTEM_TITLE', 'CRM' );

if ( !defined( 'STAFF_IP' ) ) define( 'STAFF_IP', '127.0.0.1' );

include_once 'class-ph_crm.php';
$crm = ph_crm();

if ( !defined( 'DOING_CRON' ) ) {
    /*start secure session*/
    ph_sec_session_start();
    //print_r($_SESSION);
    //session_destroy();
    //session_start();
    /*check user logged in and permissions*/
    if ( ph_script_filename() != "login.php" ) {
        if ( empty( $_SESSION[ 'user_id' ] ) ) {
            $error_message = ph_script_filename() == "index.php" ? false : array( 'message' => 'not_logged_in' ); //avoid error message for index.php as this would be annoying
            ph_redirect( 'login', $error_message );
            exit();
        }
        $ph_user = new ph_User( $_SESSION[ 'user_id' ], 'id' );
        if ( !$ph_user->is_logged_in() ) {
            if ( $ph_user->is_ip_allowed() === false )
                $error_message = 'wrong_IP';
            else
                $error_message = 'not_logged_in';
            ph_redirect( 'login', array( 'message' => $error_message, 'logout' => 'true' ) );
            exit();
        }
        //logged in, check user can use this page
        if ( !$ph_user->is_user_allowed() ) {
            ph_redirect( $ph_user->get_user_homepage(), array( 'message' => 'denied', 'page' => ph_script_filename() ) );
            exit();
        }

        //set current user for access by class code
        ph_current_user($ph_user);

        //create messages
        ph_messages( $ph_user );
    }

    /*process login*/
    if ( !empty( $_POST[ 'login-attempt' ] ) ) {
        //don't display error message over and over again.
        if ( !empty( $_GET[ 'message' ] ) && in_array( $_GET[ 'message' ], array( 'not_logged_in', 'session_ini_failed' ) ) )
            unset( $_GET[ 'message' ] );

        if ( empty( $_POST[ 'pin' ] ) ) {
            ph_messages()->add_message( 'The pin field is empty. Please try again.' );
        } else {
            $pin = ph_validate_number( $_POST[ 'pin' ] );
            if ( !$pin ) {
                ph_messages()->add_message( 'Pin should be a number only.' );
            } else {
                $user = new ph_User( $pin );
                if ( $user->login( $_POST[ 'password' ] ) )
                    ph_redirect( $user->get_user_homepage() );
                else
                    if ( !ph_messages()->is_message() )
                        ph_messages()->add_message( 'Login error, but not quite sure what it is.' );
            }
        }
    }
    if ( !defined( 'DOING_AJAX' ) )
        include 'header.php';
}
