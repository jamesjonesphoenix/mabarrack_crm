<?php

class ph_Current_User
{
    private $current_user;

    protected static $_instance = null;

    public static function instance($ph_user) {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self($ph_user);
        }
        return self::$_instance;
    }

    function __construct( $ph_user = false ) {
        return $this->set( $ph_user );
    }

    function set( $ph_user = false ) {
        if ( empty( $ph_user ) ) {
            ph_messages()->add_message( 'Couldn\'t set current user. Aborting.' );
            return false;
        }
        return $this->current_user = $ph_user;
    }

    function get() {
        if ( empty( $this->current_user ) )
            return false;
        return $this->current_user;
    }

}

function ph_current_user( $ph_user = false ) {
    return ph_Current_User::instance( $ph_user );
}