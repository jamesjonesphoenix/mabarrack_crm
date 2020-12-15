<?php

namespace Phoenix\Entity;

use Phoenix\Messages;
use Phoenix\PDOWrap;
use Phoenix\Roles;

/**
 * @property array $ipRestrictions
 *
 * Wraps User class in singleton so we only have one logged in user
 *
 * Class CurrentUser
 */
class CurrentUser extends User
{
    /**
     * @var CurrentUser|null
     */
    protected static ?CurrentUser $_instance = null;

    /**
     * @var string
     */
    private string $userBrowser;

    /**
     * @var Roles
     */
    protected Roles $roles;

    /**
     * @var array
     */
    protected array $_ipRestrictions;


    /**
     * Singletons should not be cloneable.
     */
    protected function __clone()
    {
    }

    /**
     * Singletons should not be restorable from strings.
     */
    public function __wakeup()
    {
        throw new \RuntimeException( 'Cannot unserialise a singleton.' );
    }

    /**
     * @param PDOWrap|null  $db
     * @param Messages|null $messages
     * @param Roles|null    $roles
     * @return CurrentUser
     */
    public static function instance(PDOWrap $db = null, Messages $messages = null, Roles $roles = null): CurrentUser
    {
        if ( self::$_instance === null ) {
            self::$_instance = new self( $db, $messages, $roles );
        }
        return self::$_instance;
    }

    /**
     * Entity constructor.
     *
     * @param PDOWrap|null  $db
     * @param Messages|null $messages
     * @param Roles|null    $roles
     */
    protected function __construct(PDOWrap $db = null, Messages $messages = null, Roles $roles = null)
    {
        if ( $roles !== null ) {
            $this->roles = $roles;
        }
        parent::__construct( $db, $messages );
    }

    /**
     * @return string
     */
    public function secondOrThirdPerson(): string
    {
        return 'you';
    }

    /**
     * @param string $password
     * @return bool
     */
    public function login($password = ''): bool
    {
        if ( empty( $this->id ) ) {
            return $this->addError( 'A user with this pin does not exist. Please try again.' );
        }

        if ( !$this->isIpAllowed() ) {
            return $this->addError( 'You logged in with incorrect IP. Please try again from Mabarrack Factory.' );
        }

        if ( $this->isLockedOut() ) {
            return $this->addError( "You've been locked out from logging in. Too many failed attempts." );
        }

        if ( empty( $password ) ) {
            return $this->addError( 'The password field is empty. Please try again.' );
        }

        if ( empty( $this->hash ) ) {
            return $this->addError( 'Password has not been set.' );
        }

        if (  !$this->active  ) {
            return $this->addError( 'User is inactive. An admin must reactivate your user account to allow access.' );
        }

        if ( !password_verify( $password, $this->hash ) ) {
            $this->messages->add( 'You entered an incorrect password. Please try again.' );
            // We record failed login attempt to the database
            $now = date( 'Y-m-d H:i:s' );
            $this->db->run( 'INSERT INTO login_attempts(user_id, ip, timestamp) VALUES (?, INET6_ATON(?), ?)', [$this->id, $_SERVER['REMOTE_ADDR'], $now] );
            //$this->db->add( 'login_attempts', ['user_id' => $this->id, 'ip' => $_SERVER['REMOTE_ADDR'], 'timestamp' => $now] );

            return false;
        }

        //successful login
        $_SESSION['user_id'] = preg_replace( '/[^0-9]+/', '', $this->id ); // XSS protection
        $_SESSION['login_string'] = password_hash( $this->hash . $this->getUserBrowser(), PASSWORD_BCRYPT, $this->cryptoOptions );
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
            return $this->addError( 'Incorrect IP detected. Please login from Mabarrack Factory.' );
        }

        if ( !password_verify( $this->hash . $this->getUserBrowser(), $_SESSION['login_string'] ) ) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    private function getUserBrowser(): string
    {
        if ( !empty( $this->userBrowser ) ) {
            return $this->userBrowser;
        }
        return $this->userBrowser = $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Check if staff are logging in from the Mabarrack Factory. Admins exempt from IP restriction.
     *
     * @return null|bool - NULL if ip does no matter, true if allowed, false if not allowed
     */
    public function isIpAllowed(): ?bool
    {
        $ipRestrictions = $this->ipRestrictions;
        if ( isset( $ipRestrictions['check_ip'] ) && $ipRestrictions['check_ip'] === false ) {
            return true;
        }

        if ( empty( $ipRestrictions['ip_restricted_roles'] ) ) {
            return $this->addError( 'IP restricted roles missing.' );
        }

        if ( is_string( $ipRestrictions['ip_restricted_roles'] ) ) {
            $ipRestrictions['ip_restricted_roles'] = [$ipRestrictions['ip_restricted_roles']];
        }

        if ( in_array( $this->role, $ipRestrictions['ip_restricted_roles'], false ) ) { //limit staff login to login from factory only

            if ( is_string( $ipRestrictions['allowed_ip_numbers'] ) ) {
                $ipRestrictions['allowed_ip_numbers'] = [$ipRestrictions['allowed_ip_numbers']];
            }

            if ( !in_array( $_SERVER['REMOTE_ADDR'], $ipRestrictions['allowed_ip_numbers'], true ) ) {
                return $this->addError( 'Incorrect IP detected. Please login from approved location.' );
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getHomePage(): string
    {
        if ( $this->role === 'admin' ) {
            return 'index.php';
        }
        if ( $this->role === 'staff' ) {
            return 'employee.php';
        }
        return '';
    }

    /**
     *
     */
    public function logout(): void
    {
        $_SESSION = []; // Unset all session values
        $params = session_get_cookie_params(); // get session parameters
        setcookie( // Delete the actual cookie.
            session_name(),
            '', time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly'] );
        session_destroy();  // Destroy session
        $this->messages->add( 'You have successfully logged out.', 'primary' );
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

        $attempts = $this->db->run( 'SELECT INET6_NTOA(ip),timestamp FROM login_attempts WHERE user_id = ? AND timestamp > ?', [$this->id, $validAttempts] )->fetchAll();
        return count( $attempts ) > 10;
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

        if ( in_array( $capability, $this->roles->getRoleCapabilities( $this->role ), true ) ) {
            return true;
        }
        return false;
    }

    /**
     * @param array $ipRestrictions
     * @return array
     */
    protected function ipRestrictions(array $ipRestrictions = []): array
    {
        if ( !empty( $ipRestrictions ) ) {
            $this->_ipRestrictions = $ipRestrictions;
        }
        return $this->_ipRestrictions ?? [];
    }
}