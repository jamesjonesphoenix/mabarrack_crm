<?php

namespace Phoenix;

require_once __DIR__ . '/../vendor/autoload.php';

include __DIR__ . '/../config.php';

//Default DB definitions
if ( !defined( 'DB_HOST' ) ) {
    define( 'DB_HOST', 'localhost' );
}
if ( !defined( 'DB_USER' ) ) {
    define( 'DB_USER', 'root' );
}
if ( !defined( 'DB_PASSWORD' ) ) {
    define( 'DB_PASSWORD', '' );
}
if ( !defined( 'DB_NAME' ) ) {
    define( 'DB_NAME', 'mabdb' );
}
if ( !defined( 'DB_PORT' ) ) {
    define( 'DB_PORT', '3306' );
}
//Default parameters
if ( !defined( 'USING_SSL' ) ) {
    define( 'USING_SSL', false );
}
if ( !defined( 'SYSTEM_TITLE' ) ) {
    define( 'SYSTEM_TITLE', 'CRM' );
}
if ( !defined( 'ALLOWED_IP_NUMBERS' ) ) {
    define( 'ALLOWED_IP_NUMBERS', '127.0.0.1' );
}
if ( !defined( 'IP_RESTRICTED_ROLES' ) ) {
    define( 'IP_RESTRICTED_ROLES', 'staff' );
}

//$crm = CRM::instance();

if ( !defined( 'DOING_CRON' ) ) {
    /*start secure session*/
    ph_sec_session_start();
    //print_r($_SESSION);
    //session_destroy();
    //session_start();
    /*check user logged in and permissions*/
    if ( ph_script_filename() !== 'login.php' ) {
        if ( empty( $_SESSION['user_id'] ) ) {
            $errorMessage = ph_script_filename() === 'index.php' ? false : array('message' => 'not_logged_in'); //avoid error message for index.php as this would be annoying
            ph_redirect( 'login', $errorMessage );
            exit();
        }

        //set current user for access by class code
        $ph_user = CurrentUser::instance( PDOWrap::instance(), Messages::instance(), $_SESSION['user_id'], 'id' );
        if ( !$ph_user->isLoggedIn() ) {
            if ( !$ph_user->isIpAllowed() ) {
                $errorMessage = 'wrong_IP';
            } else {
                $errorMessage = 'not_logged_in';
            }
            ph_redirect( 'login', array('message' => $errorMessage, 'logout' => 'true') );
            exit();
        }
        //logged in, check user can use this page
        if ( !$ph_user->isUserAllowed() ) {
            ph_redirect( $ph_user->getUserHomepage(), array('message' => 'denied', 'page' => ph_script_filename()) );
            exit();
        }

        //create messages
        ph_messages( $ph_user );
    }

    /*process login*/
    if ( !empty( $_POST['login-attempt'] ) ) {
        //don't display error message over and over again.
        if ( !empty( $_GET['message'] ) && in_array( $_GET['message'], array('not_logged_in', 'session_ini_failed') ) ) {
            unset( $_GET['message'] );
        }

        if ( empty( $_POST['pin'] ) ) {
            ph_messages()->add( 'The pin field is empty. Please try again.' );
        } else {
            $pin = ph_validate_number( $_POST['pin'] );
            if ( !$pin ) {
                ph_messages()->add( 'Pin should be a number only.' );
            } else {

                $user = new User( PDOWrap::instance(), Messages::instance() );
                $user->init( $pin );

                if ( $user->login( $_POST['password'] ) ) {
                    //$_SESSION['message'] = 'Logged in like a boss';
                    $_SESSION['message'] = 'loggedIn';
                    ph_redirect( $user->getUserHomepage() );
                } elseif ( !ph_messages()->isMessage() ) {
                    ph_messages()->add( 'Login error, but not quite sure what it is.' );
                }
            }
        }
    }
    if ( !defined( 'DOING_AJAX' ) ) {
        ph_get_template_part( 'header' );
    }
}
