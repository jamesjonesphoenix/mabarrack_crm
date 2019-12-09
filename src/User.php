<?php

namespace Phoenix;

/**
 * @property array $cryptoOptions
 * @property string $hash
 * @property string $name
 * @property integer $pin
 * @property integer $rate
 * @property string $role
 * @property Shift[] $shifts
 *
 * Class User
 *
 * @package Phoenix
 */
class User extends Entity
{
    /**
     * @var array
     */
    protected $_cryptoOptions = array('cost' => 12);

    /**
     * @var string
     */
    protected $_hash;

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var integer
     */
    protected $_pin;

    /**
     * @var string
     */
    protected $_role;

    /**
     * @var integer
     */
    protected $_rate;

    /**
     * @var Shift[]
     */
    protected $_shifts;

    /**
     * @var bool|mixed
     */
    private $userBrowser;

    /**
     * @var string
     */
    protected $_tableName = 'users';

    /**
     * Query DB for user with inputted pin number or ID and fill out class properties
     *
     * @param array|int $input
     * @param string $field
     * @return bool
     */
    public function init($input = null, string $field = 'pin'): bool
    {
        $this->userBrowser = $_SERVER['HTTP_USER_AGENT'];

        if ( is_numeric( $input ) ) {

            switch( strtolower( $field ) ) {
                case 'id':
                    $search = $input ?? $this->id;
                    $column = 'ID';
                    break;
                case 'pin':
                default:
                    $search = $input ?? $this->pin;
                    $column = 'pin';
                    break;
            }

            if ( empty( $search ) || !ph_validate_number( $search ) ) {
                return false;
            }

            $row = $this->db->getRow( $this->tableName, array($column => $search) );
        } else {
            $row = $input;
        }
        if ( empty( $row['ID'] ) ) {
            return $this->exists = false;
        }

        $this->id = $row['ID'] ?? 0;
        $this->name = $row['name'] ?? '';
        $this->pin = !empty( $row['pin'] ) ? $row['pin'] : 0;
        $this->role = $row['type'] ?? '';
        $this->rate = $row['rate'] ?? 0;
        $this->hash = $row['password'] ?? '';

        return $this->exists = true;
    }

    /**
     * @param string $hash
     * @return string
     */
    protected function hash(string $hash = ''): string
    {
        if ( !empty( $hash ) ) {
            $this->_hash = $hash;
        }
        return $this->_hash ?? '';
    }

    /**
     * @param int $pin
     * @return int
     */
    protected function pin(int $pin = 0): int
    {
        if ( !empty( $pin ) ) {
            $this->_pin = $pin;
        }
        return $this->_pin ?? 0;
    }

    /**
     * @param int $rate
     * @return int
     */
    protected function rate(int $rate = 0): int
    {
        if ( !empty( $rate ) ) {
            $this->_rate = $rate;
        }
        return $this->_rate ?? 0;
    }


    /**
     * @param string $name
     * @return string
     */
    protected function name(string $name = ''): string
    {
        if ( !empty( $name ) ) {
            $this->_name = $name;
        }
        return $this->_name ?? '';
    }

    /**
     * @param string $role
     * @return string
     */
    protected function role(string $role = ''): string
    {
        if ( !empty( $role ) ) {
            $this->_role = $role;
        }
        return $this->_role ?? '';
    }

    /**
     * @param array $cryptoOptions
     * @return array
     */
    protected function cryptoOptions(array $cryptoOptions = []): array
    {
        if ( !empty( $cryptoOptions ) ) {
            $this->_cryptoOptions = $cryptoOptions;
        }
        return $this->_cryptoOptions ?? [];
    }

    /**
     * @param string $password
     * @return bool
     */
    public function login($password = ''): bool
    {
        if ( empty( $this->id ) ) {
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

        if ( empty( $this->hash ) ) {
            $this->messages->add( 'Password has not been set.' );
            return false;
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
        $_SESSION['login_string'] = password_hash( $this->hash . $this->userBrowser, PASSWORD_BCRYPT, $this->cryptoOptions );
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
        if ( !password_verify( $this->hash . $this->userBrowser, $_SESSION['login_string'] ) ) {
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
        if ( in_array( $this->role, $ipRestrictedRoles, false ) ) {
            //limit staff login to login from factory only
            $allowedIPs = is_string( ALLOWED_IP_NUMBERS ) ? array(ALLOWED_IP_NUMBERS) : ALLOWED_IP_NUMBERS;

            if ( !in_array( $_SERVER['REMOTE_ADDR'], $allowedIPs, true ) ) {
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
        $roles = Roles::instance();
        $capabilities = $roles->getRoleCapabilities( $this->role );

        if ( in_array( $capability, $capabilities, true ) ) {
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

        $attempts = $this->db->run( 'SELECT INET6_NTOA(ip),timestamp FROM login_attempts WHERE user_id = ? AND timestamp > ?', [$this->id, $validAttempts] )->fetchAll();
        return count( $attempts ) > 10;
    }

    /**
     * @param array $shifts
     * @return Shift[]
     */
    protected function shifts(array $shifts = []): array
    {
        if ( !empty( $shifts ) ) {
            $this->_shifts = $shifts;
        }
        return $this->_shifts ?? [];
    }

}