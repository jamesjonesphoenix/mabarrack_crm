<?php

class ph_User
{

    protected $db;
    protected $data;

    private $crypto_options = array( 'cost' => 12 );
    private $user_browser = false;

    public $id;
    public $name;
    public $pin;
    public $role;
    public $rate;
    public $hash;
    public $user_homepage;
    public $capabilities = array();

    /**
     * ph_User constructor.
     *
     * @param bool $value
     * @param bool $field can be id or pin. defaults to pin
     */
    function __construct( $value = false, $field = false ) {
        $this->db = ph_pdo();
        $this->user_browser = $_SERVER[ 'HTTP_USER_AGENT' ];

        //echo ' ' . password_hash( 'blag', PASSWORD_BCRYPT, $this->crypto_options );

        return $this->init( $value, $field );
    }

    function init( $value = false, $field = false ) {
        switch ( $field ) {
            case 'id':
                $search = !empty( $value ) ? $value : $this->get_id();
                $column = "ID";
                break;
            case 'pin':
            case false:
            default:
                $search = !empty( $value ) ? $value : $this->get_pin();
                $column = "pin";
                break;
        }
        if ( empty( $search ) || !ph_validate_number( $search ) )
            return false;

        $user = $this->db->run( "SELECT * FROM users WHERE " . $column . " = ?", [ $search ] )->fetchAll();
        if ( !empty( $user ) && count( $user ) == 1 ) {
            $this->id = !empty( $user[ 0 ][ 'ID' ] ) ? $user[ 0 ][ 'ID' ] : 0;
            $this->name = !empty( $user[ 0 ][ 'name' ] ) ? $user[ 0 ][ 'name' ] : '';
            $this->pin = !empty( $user[ 0 ][ 'pin' ] ) ? $user[ 0 ][ 'pin' ] : '';
            $this->role = !empty( $user[ 0 ][ 'type' ] ) ? $user[ 0 ][ 'type' ] : '';
            $this->rate = !empty( $user[ 0 ][ 'rate' ] ) ? $user[ 0 ][ 'rate' ] : 0;
            $this->hash = !empty( $user[ 0 ][ 'password' ] ) ? $user[ 0 ][ 'password' ] : '';
            $this->get_role_capabilities();
            return true;
        }
        return false;
    }

    public function get_hash( $value = false, $field = false ) {
        if ( empty( $this->hash ) ) {
            if ( !$value )
                $value = $this->get_pin();
            if ( $this->init( $value, $field ) )
                return !empty( $this->hash ) ? $this->hash : false;
        }
        return $this->hash;
    }

    function get_pin( $id = false ) {
        if ( empty( $this->pin ) ) {
            if ( !$id )
                return false;
            else
                return false;
        }
        return $this->pin;
    }

    function get_id( $pin = false ) {
        if ( empty( $this->id ) ) {
            if ( !$pin )
                return false;
            else
                return false;
        }
        return $this->id;
    }

    function get_name() {
        return !empty( $this->name ) ? $this->name : false;
    }

    function get_role( $value = false, $field = false ) {
        if ( empty( $this->role ) ) {
            if ( !$value )
                $value = $this->get_pin();
            if ( $this->init( $value, $field ) )
                return !empty( $this->role ) ? $this->role : false;
        }
        return $this->role;
    }

    function get_user_homepage( $value = false, $field = false ) {
        if ( empty( $this->user_homepage ) ) {
            $this->user_homepage = $this->db->run( "SELECT homepage FROM user_access WHERE type = ?", [ $this->get_role( $value, $field ) ] )->fetch()[ 'homepage' ];
        }
        return $this->user_homepage;
    }

    function get_crypto_options() {
        if ( empty( $this->crypto_options ) )
            return false;
        return $this->crypto_options;
    }

    function login( $password = false ) {
        if ( !$this->get_id() ) {
            ph_messages()->add_message( 'A user with this pin does not exist. Please try again.' );
            return false;
        }

        if ( $this->is_ip_allowed() )
            $staff_login = true;
        elseif ( $this->is_ip_allowed() === false ) {
            ph_messages()->add_message( 'Incorrect IP detected. Please login from Mabarrack Factory.' );
            return false;
        }

        if ( $this->is_locked_out() ) {
            ph_messages()->add_message( 'You\'ve been locked out from logging in. Too many failed attempts.' );
            return false;
        }

        if ( empty( $staff_login ) && empty( $password ) ) {
            ph_messages()->add_message( 'Password field is empty.' );
            return false;
        }

        if ( !empty( $staff_login ) || ( !empty( $password ) && password_verify( $password, $this->get_hash() ) ) ) {
            //successful login
            $_SESSION[ 'user_id' ] = preg_replace( "/[^0-9]+/", "", $this->get_id() ); // XSS protection
            $_SESSION[ 'login_string' ] = password_hash( $this->get_hash() . $this->user_browser, PASSWORD_BCRYPT, $this->crypto_options );
            return true;
        }
        ph_messages()->add_message( 'You entered an incorrect password. Please try again.' );
        // Password is not correct
        // We record this attempt in the database
        $now = date( 'Y-m-d H:i:s' );
        $this->db->run( "INSERT INTO login_attempts(user_id, ip, timestamp) VALUES (?, INET6_ATON(?), ?)", [ $this->get_id(), $_SERVER[ 'REMOTE_ADDR' ], $now ] );
        return false;
    }

    function is_logged_in() {
        if ( !isset( $_SESSION[ 'user_id' ], $_SESSION[ 'login_string' ] ) )
            return false;

        if ( $this->is_ip_allowed() === false ) {
            ph_messages()->add_message( 'Incorrect IP detected. Please login from Mabarrack Factory.' );
            return false;
        }
        if ( !password_verify( $this->get_hash() . $this->user_browser, $_SESSION[ 'login_string' ] ) ) {
            return false;
        }
        return true;
    }

    /**
     * Check if staff are logging in from the Mabarrack Factory. Admins exempt from IP restriction.
     *
     * @return bool - NULL if ip does no matter, true if allowed, false if not allowed
     */
    function is_ip_allowed() {
        if ( $this->get_role() == 'staff' ) {
            //limit staff login to login from factory only
            if ( !is_array( STAFF_IP ) )
                $staff_ips = array( STAFF_IP );
            else
                $staff_ips = STAFF_IP;
            foreach ( $staff_ips as $ip ) {
                if ( $_SERVER[ 'REMOTE_ADDR' ] == $ip )
                    return true;
            }
            return false;
        }
        return null;
    }

    function get_role_capabilities() {
        if ( empty( $this->capabilities ) ) {
            $this->capabilities = ph_roles()->get_role_capabilities( $this->get_role() );
        }
        return $this->capabilities;
    }


    function is_user_allowed( $cap = false ) {
        if ( !$cap )
            $cap = ph_script_filename();
        $capabilities = $this->get_role_capabilities();
        if ( in_array( $cap, $capabilities ) ) {
            return true;
        }
        return false;
    }


    function logout() {
        // Unset all session values
        $_SESSION = array();

        // get session parameters
        $params = session_get_cookie_params();

        // Delete the actual cookie.
        setcookie( session_name(),
            '', time() - 42000,
            $params[ "path" ],
            $params[ "domain" ],
            $params[ "secure" ],
            $params[ "httponly" ] );

        // Destroy session
        session_destroy();
    }

    function is_locked_out() {
        // Get timestamp of current time
        // All login attempts are counted from the past 12 hours.
        $valid_attempts = date( 'Y-m-d H:i:s', strtotime( '-12 hours' ) );

        //$valid_attempts = $now - ( 12 * 60 * 60 );

        $attempts = $this->db->run( "SELECT INET6_NTOA(ip),timestamp FROM login_attempts WHERE user_id = ? AND timestamp > ?", [ $this->get_id(), $valid_attempts ] )->fetchAll();

        if ( true ) {
            // If there have been more than 10 failed logins
            if ( count( $attempts ) > 10 ) {
                return true;
            } else {
                return false;
            }
        }
    }
}