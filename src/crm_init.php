<?php

namespace Phoenix;

require_once __DIR__ . '/../vendor/autoload.php';

(new Init())->startUp();
/*
if ( !defined( 'DOING_CRON' ) ) {
    secureSessionStart();
    $userID = $_SESSION['user_id'];
    $scriptFilename = basename( $_SERVER['SCRIPT_FILENAME'] );
    $roles = new Roles;

    $currentUserFactory = new CurrentUserFactory( $db, $messages );
    //Check whether the user is already logged in
    if ( isset( $userID ) && basename( $_SERVER['SCRIPT_FILENAME'] ) === 'login.php' ) { //logged in, redirect to main page
        $user = $currentUserFactory->getEntity( $userID );

        if ( $user === null ) {
            $messages->add( 'Could not get current user with ID: <strong>' . $userID . '</strong>.' );
        } else {
            if ( $user->isLoggedIn() ) {
                if ( !empty( $_GET['logout'] ) && $_GET['logout'] === 'true' ) {
                    $user->logout();
                    $messages->add( 'You have successfully logged out.', 'primary' );
                } else {
                    redirect( $roles->getHomePage( $user->role ) );
                }
            }
        }
    }

    $deniedNotLoggedIn = 'You cannot access that page until you are logged in. Please login.';
    /*check user logged in and permissions
    if ( $scriptFilename !== 'login.php' ) {
        if ( empty( $userID ) ) {
            if ( $scriptFilename !== 'index.php' ) { //avoid error message for index.php as this would be annoying
                $messages->add( $deniedNotLoggedIn );
            }
            redirect( 'login' );
        }

        //set current user for access by class code
        $user = $currentUserFactory->getEntity( $userID );
        if ( $user === null ) {
            $messages->add( 'Could not get current user with ID: <strong>' . $userID . '</strong>.' );
            redirect( 'login' );
        }
        if ( !$user->isLoggedIn() ) {
            $messages->add( $deniedNotLoggedIn );
            redirect( 'login', ['logout' => 'true'] );
        }
        //logged in, check user can use this page
        if ( !$user->isUserAllowed() ) {
            if ( $scriptFilename !== 'index.php' ) {
                $messageString = 'You were redirected to the ' . $user->role . ' homepage because you are not allowed to visit <strong>' . $scriptFilename . '</strong>.';
                $messages->add( $messageString, 'warning' );
            }
            redirect( $roles->getHomePage( $user->role ), ['page' => $scriptFilename] );
        }


    }

    /*Process Login
    if ( !empty( $_POST['login-attempt'] ) ) {
        //don't display error message over and over again.
        /*
        if ( !empty( $_GET['messages'] ) && in_array( $_GET['messages'], array('not_logged_in', 'session_ini_failed') ) ) {
            unset( $_GET['messages'] );
        }

        if ( empty( $_POST['pin'] ) ) {
            $messages->add( 'The pin field is empty. Please try again.' );
        } else {
            $pin = phValidateID( $_POST['pin'] );
            if ( empty( $pin ) ) {
                $messages->add( 'Pin should be a number only.' );
            } else {
                $user = $currentUserFactory->getUserFromPin( $pin );
                if ( $user->login( $_POST['password'] ) ) {
                    $messages->add( 'Logged in successfully.', 'success' );
                    redirect( $roles->getHomePage( $user->role ) );
                } elseif ( !$messages->isMessage() ) {
                    $messages->add( 'Login error, but not quite sure what it is.' );
                }
            }
        }
    }

    /*Process Logout


    if ( !defined( 'DOING_AJAX' ) ) {
        //you
        //getTemplatePart( 'header' );

    }
}
*/