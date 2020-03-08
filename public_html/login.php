<?php

namespace Phoenix;

include '../src/crm_init.php';
//Check whether the user is already logged in
if ( isset( $_SESSION['user_id'] ) ) { //logged in, redirect to main page
    $ph_user = CurrentUser::instance( PDOWrap::instance(), Messages::instance(), $_SESSION['user_id'], 'id' );
    if ( $ph_user->isLoggedIn() ) {
        if ( !empty( $_GET['logout'] ) && $_GET['logout'] === 'true' ) {
            $ph_user->logout();
            ph_messages()->add( 'You have successfully logged out.', 'primary' );
        } else {
            $roles = new Roles;
            ph_redirect( $roles->getHomePage( $ph_user->role ) );
        }
    }
}

if ( !empty( $_GET['message'] ) ) {

    switch( $_GET['message'] ) {
        case 'session_ini_failed':
            ph_messages()->add( 'Could not initiate a safe session (ini_set)' );
            break;
        case 'not_logged_in':
            ph_messages()->add( 'You cannot access that page until you are logged in. Please login.' );
            break;
        case 'login_timed_out':
            ph_messages()->add( 'Your login timed out due to inactivity. Please login again.' );
            break;
        case 'wrong_IP':
            ph_messages()->add( 'You logged in with incorrect IP. Please try again from Mabarrack Factory.' );
            break;
        default:
            ph_messages()->add( 'Error, but not quite sure what it is.' );
            break;
    }
}

?>
    <div class="row" style="text-align: center;">
        <div class="col-md-12"><img src="img/logo.png" class="logo"/>
            <h1 class='crm-title' style="text-align: center"><?php echo SYSTEM_TITLE; ?></h1>
        </div>
    </div>
    <div class="row login-panel">
        <div class="col-md-12">
            <h2>LOGIN</h2>
            <?php ph_messages()->display(); ?>
            <form method='post' class='form' id="loginform">
                <div class="login-fields">
                    <label for="pin">Your Pin</label>
                    <input id="pin-field" name='pin' type='text' class='form-control' data-validation='number'
                           maxlength="4"
                           autofocus/>
                </div>
                <div class="login-fields">
                    <label for="password">Your Password</label>
                    <input id="password-field" name='password' type='password' class='form-control' autofocus/>
                </div>
                <input id="login-button" type='submit' value='Login' class='btn btn-default'>
                <input name="login-attempt" type='hidden' value='submit'>
            </form>
        </div>
    </div>
    </div>

    <?php
//  $blegh = array('bla','ob','gib');
//  echo reset($blegh);
ph_get_template_part( 'footer' ); ?>