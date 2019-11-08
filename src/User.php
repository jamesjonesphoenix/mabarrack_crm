<?php

namespace Phoenix;

/**
 * Class User
 */
class User extends Base
{
    /**
     * @var
     */
    protected $data;

    /**
     * @var array
     */
    private $crypto_options = array('cost' => 12);

    /**
     * @var bool|mixed
     */
    private $user_browser;

    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $name;

    /**
     * @var
     */
    public $pin;

    /**
     * @var
     */
    public $role;

    /**
     * @var
     */
    public $rate;

    /**
     * @var
     */
    public $hash;

    /**
     * @var
     */
    public $user_homepage;

    /**
     * @var array
     */
    public $capabilities = array();

    /**
     * User constructor.
     *
     * @param PDOWrap|null $db
     * @param Messages|null $messages
     * @param string $value
     * @param string $field
     */
    public function __construct(PDOWrap $db = null, Messages $messages = null, string $value = '', string $field = 'pin')
    {
        $this->user_browser = $_SERVER['HTTP_USER_AGENT'];
        //echo ' ' . password_hash( '', PASSWORD_BCRYPT, $this->crypto_options );

        parent::__construct( $db, $messages );

        if ( !empty( $value ) ) {
            $this->init( $value, $field );
        }
    }

    /**
     * @param int $value
     * @param string $field
     * @return bool
     */
    public function init(int $value = 0, string $field = 'pin'): bool
    {
        if ( $value === 0 ) {
            return false;
        }

        switch( $field ) {
            case 'id':
                $search = $value ?? $this->getID();
                $column = 'ID';
                break;
            case 'pin':
            default:
                $search = $value ?? $this->getPin();
                $column = 'pin';
                break;
        }

        if ( empty( $search ) || !ph_validate_number( $search ) ) {
            return false;
        }

        $user = $this->db->getRow( 'users', array($column => $search) );
        if ( empty( $user ) ) {
            return false;
        }

        $this->id = $user['ID'] ?? 0;
        $this->name = $user['name'] ?? '';
        $this->pin = $user['pin'] ?? '';
        $this->role = $user['type'] ?? '';
        $this->rate = $user['rate'] ?? 0;
        $this->hash = $user['password'] ?? '';

        $roles = new Roles;
        $this->capabilities = $roles->getRoleCapabilities( $this->getRole() );

        return true;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash ?? '';
    }

    /**
     * @return int
     */
    public function getPin(): int
    {

        return $this->pin ?? 0;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id ?? 0;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        //d($this->role);
        return $this->role ?? '';
    }

    /**
     * @return mixed
     */
    public function getUserHomepage()
    {
        if ( !empty( $this->user_homepage ) ) {
            return $this->user_homepage;
        }
        $roles = new Roles;
        return $this->user_homepage = $roles->getHomePage($this->getRole());
        //return $this->user_homepage = $this->db->run( 'SELECT homepage FROM user_access WHERE type = ?', [$this->getRole()] )->fetch()['homepage'] ?? '';
    }

    /**
     * @return array|bool
     */
    public function getCryptoOptions()
    {
        if ( empty( $this->crypto_options ) ) {
            return false;
        }
        return $this->crypto_options;
    }

    /**
     * @param string $password
     * @return bool
     */
    public function login($password = ''): bool
    {
        if ( empty( $this->getID() ) ) {
            $this->messages->add( 'A user with this pin does not exist. Please try again.' );
            return false;
        }

        if ( !$this->isIpAllowed() ) {
            return false;
        }

        if ( $this->isLockedOut() ) {
            $this->messages->add( 'You\'ve been locked out from logging in. Too many failed attempts.' );
            return false;
        }

        if ( empty( $password ) ) {
            $this->messages->add( 'Password field is empty.' );
            return false;
        }

        if ( empty( $this->getHash() ) ) {
            $this->messages->add( 'Password has not been set.' );
            return false;
        }

        if ( !password_verify( $password, $this->getHash() ) ) {
            $this->messages->add( 'You entered an incorrect password. Please try again.' );
            // We record failed login attempt to the database
            $now = date( 'Y-m-d H:i:s' );
            $this->db->run( 'INSERT INTO login_attempts(user_id, ip, timestamp) VALUES (?, INET6_ATON(?), ?)', [$this->getID(), $_SERVER['REMOTE_ADDR'], $now] );
            return false;
        }

        //successful login
        $_SESSION['user_id'] = preg_replace( '/[^0-9]+/', '', $this->getID() ); // XSS protection
        $_SESSION['login_string'] = password_hash( $this->getHash() . $this->user_browser, PASSWORD_BCRYPT, $this->crypto_options );
        return true;

    }

    /**
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        if ( !isset( $_SESSION['user_id'], $_SESSION['login_string'] ) ) {
            return false;
        }

        if ( $this->isIpAllowed() === false ) {
            $this->messages->add( 'Incorrect IP detected. Please login from Mabarrack Factory.' );
            return false;
        }
        if ( !password_verify( $this->getHash() . $this->user_browser, $_SESSION['login_string'] ) ) {
            return false;
        }
        return true;
    }

    /**
     * Check if staff are logging in from the Mabarrack Factory. Admins exempt from IP restriction.
     *
     * @return null|bool - NULL if ip does no matter, true if allowed, false if not allowed
     */
    public function isIpAllowed(): ?bool
    {
        if ( defined( 'CHECK_IP' ) && !CHECK_IP ) {
            return true;
        }

        if ( !defined( 'IP_RESTRICTED_ROLES' ) ) {
            $this->messages->add( 'IP_RESTRICTED_ROLES missing.' );
            return false;
        }
        $ipRestrictedRoles = is_string( IP_RESTRICTED_ROLES ) ? array(IP_RESTRICTED_ROLES) : IP_RESTRICTED_ROLES;
        if ( in_array( $this->getRole(), $ipRestrictedRoles, true ) ) {
            //limit staff login to login from factory only
            $allowedIPs = is_string( ALLOWED_IP_NUMBERS ) ? array(ALLOWED_IP_NUMBERS) : ALLOWED_IP_NUMBERS;

            if ( in_array( !$_SERVER['REMOTE_ADDR'], $allowedIPs, true ) ) {
                $this->messages->add( 'Incorrect IP detected. Please login from approved location.' );
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $capability
     * @return bool
     */
    public function isUserAllowed($capability = ''): bool
    {
        if ( empty( $capability ) ) {
            $capability = basename( $_SERVER['SCRIPT_FILENAME'], false );
        }
        if ( in_array( $capability, $this->capabilities, true ) ) {
            return true;
        }
        return false;
    }

    /**
     *
     */
    public function logout(): void
    {
        // Unset all session values
        $_SESSION = array();

        // get session parameters
        $params = session_get_cookie_params();

        // Delete the actual cookie.
        setcookie( session_name(),
            '', time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly'] );

        // Destroy session
        session_destroy();
    }

    /**
     * @return bool
     */
    public function isLockedOut(): bool
    {
        // Get timestamp of current time
        // All login attempts are counted from the past 12 hours.
        $validAttempts = date( 'Y-m-d H:i:s', strtotime( '-12 hours' ) );

        //$validAttempts = $now - ( 12 * 60 * 60 );

        $attempts = $this->db->run( 'SELECT INET6_NTOA(ip),timestamp FROM login_attempts WHERE user_id = ? AND timestamp > ?', [$this->getID(), $validAttempts] )->fetchAll();
        return count( $attempts ) > 10;
    }
}