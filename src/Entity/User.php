<?php

namespace Phoenix\Entity;

use Phoenix\DateTimeUtility;
use Phoenix\Messages;
use Phoenix\PDOWrap;
use Phoenix\Roles;
use function Phoenix\phValidateID;

/**
 * @property array   $cryptoOptions
 * @property string  $hash
 * @property string  $name
 * @property integer $pin
 * @property integer $rate
 * @property string  $role
 * @property Shifts  $shifts
 *
 * Class User
 *
 * @package Phoenix
 */
class User extends Entity
{
    /**
     * @var string Fontawesome icon
     */
    protected string $icon = 'user';

    /**
     * @var array
     */
    protected array $_cryptoOptions = ['cost' => 12];

    /**
     * @var string
     */
    protected string $_hash;

    /**
     * @var string
     */
    protected string $_name;

    /**
     * @var integer
     */
    protected int $_pin;

    /**
     * @var string
     */
    protected string $_role;

    /**
     * @var integer
     */
    protected int $_rate;

    /**
     * @var Shifts
     */
    protected Shifts $_shifts;

    /**
     * @var string
     */
    private string $userBrowser;

    /**
     * Map of Database columns. Key is column name, 'property' is matching Class property. Don't need to include ID column in this array.
     *
     * @var array
     */
    protected array $_columns = [
        'name' => [
            'type' => 'name',
            'required' => true
        ],
        'pin' => [
            'type' => 'id',
            'required' => true
        ],
        'type' => [
            'type' => 'string',
            'required' => true,
            'property' => 'role'
        ],
        'rate' => [
            'type' => 'float',
            'required' => true
        ],
        'password' => [
            'type' => 'password',
            'required' => true,
            'property' => 'hash'
        ],
    ];

    /**
     * @var Roles
     */
    private Roles $roles;

    /**
     * Entity constructor.
     *
     * @param PDOWrap|null  $db
     * @param Messages|null $messages
     * @param Roles|null    $roles
     */
    public function __construct(PDOWrap $db = null, Messages $messages = null, Roles $roles = null)
    {
        if ( $roles !== null ) {
            $this->roles = $roles;
        }
        parent::__construct( $db, $messages );
    }

    /**
     * Identical to parent init but encrypts password input
     *
     * @param array|int $input
     * @return $this
     */
    public function init($input = []): self
    {
        if ( !empty( $input['unencrypted-password'] ) && is_string( $input['unencrypted-password'] ) ) { //Changing password
            $input['password'] = password_hash( $input['unencrypted-password'], PASSWORD_BCRYPT, $this->cryptoOptions );
        }
        return parent::init( $input );
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
     * @param int|null $pin
     * @return int
     */
    protected function pin(int $pin = null): ?int
    {
        if ( $pin !== null ) {
            $this->_pin = $pin;
        }
        return $this->_pin ?? null;
    }

    /**
     * @param int|null $rate
     * @return int
     */
    protected function rate(int $rate = null): ?int
    {
        if ( $rate !== null ) {
            $this->_rate = $rate;
        }
        return $this->_rate ?? null;
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
     * @return string
     */
    public function getNamePossessive(): string
    {
        $name = $this->name;
        if ( empty( $name ) ) {
            return '';
        }
        if ( substr( $name, -1 ) === 's' ) {
            return $name . "'";
        }
        return $name . "'s";
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
            return $this->addError( 'IP_RESTRICTED_ROLES missing.' );
        }

        $ipRestrictedRoles = is_string( IP_RESTRICTED_ROLES ) ? [IP_RESTRICTED_ROLES] : IP_RESTRICTED_ROLES;
        if ( in_array( $this->role, $ipRestrictedRoles, false ) ) {
            //limit staff login to login from factory only
            $allowedIPs = is_string( ALLOWED_IP_NUMBERS ) ? [ALLOWED_IP_NUMBERS] : ALLOWED_IP_NUMBERS;

            if ( !in_array( $_SERVER['REMOTE_ADDR'], $allowedIPs, true ) ) {
                return $this->addError( 'Incorrect IP detected. Please login from approved location.' );
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

        if ( in_array( $capability, $this->roles->getRoleCapabilities( $this->role ), true ) ) {
            return true;
        }
        return false;
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
            return 'worker.php';
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


    /*
    protected function shifts(array $shifts = []): array
    {
        if ( empty( $shifts ) ) {
            return $this->_shifts ?? [];
        }
        return $this->_shifts = $shifts;
    }
    */

    /**
     * @param Shift[] $shifts
     * @return Shifts|null
     */
    protected function shifts(array $shifts = []): ?Shifts
    {
        if ( empty( $shifts ) ) {
            return $this->_shifts ?? new Shifts();
        }
        return $this->_shifts = new Shifts( $shifts );
    }

    /**
     * Assumes shifts are sorted from latest to earliest which they should be by shifts()
     *
     * @param int $numberOfJobs
     * @return Job[]
     */
    public function getLastWorkedJobs(int $numberOfJobs = 1): array
    {
        if ( $numberOfJobs < 1 ) {
            $this->addError( 'getLastWorkedJobs() called with invalid number of jobs.' );
            return [];
        }
        $this->shifts->orderLatestToEarliest();
        foreach ( $this->shifts->getAll() as $shift ) {
            $jobID = $shift->job->id;
            //if ( empty( $jobs[$jobID] ) && $jobID !== 0 ) {
            if ( empty( $jobs[$jobID] ) ) {
                $jobs[$jobID] = $shift->job;
                $jobs[$jobID]->shifts = [$shift->id => $shift]; //Reduce job shifts to just one shift - the most recent one.
                if ( count( $jobs ) === $numberOfJobs ) {
                    break;
                }
            }
        }
        return $jobs ?? [];
    }

    /**
     * Clocks off previous shift and starts new shift
     *
     * @param int      $activityID
     * @param int|null $jobID
     * @param int|null $furnitureID
     * @param string   $comment
     * @return bool|Shift
     * @throws \Exception
     */
    public function startNewShift(int $activityID, int $jobID, $furnitureID = null, string $comment = '')
    {
        if ( !empty( $this->healthCheck() ) ) {
            return false;
        }
        $newShift = (new ShiftFactory( $this->db, $this->messages ))->getNew();
        $newShift->worker = $this;
            $newShift->job = (new JobFactory( $this->db, $this->messages ))->getEntity( $jobID );
            if ( $newShift->job->id === null ) {
                $errors[] = 'Job <span class="badge badge-danger">ID: ' . $jobID . "</span> doesn't exist.";
            }

        $newShift->activity = (new ActivityFactory( $this->db, $this->messages ))->getEntity( $activityID );
        if ( $newShift->activity->id === null ) {
            $errors[] = 'Activity <span class="badge badge-danger">ID: ' . $activityID . "</span> doesn't exist.";
        }

        if ( $furnitureID !== null && $furnitureID !== '') {
            $newShift->furniture = (new FurnitureFactory( $this->db, $this->messages ))->getEntity( $furnitureID );
            if ( $newShift->furniture->id === null ) {
                $errors[] = 'Furniture <span class="badge badge-danger">ID: ' . $furnitureID . "</span> doesn't exist.";
            }
        }
        if ( !empty( $errors ) ) {
            return $this->addError( '<h5 class="alert-heading">Can\'t ' . $newShift->getActionString( 'present', 'start' ) . ':</h5>' . parent::healthCheck( $errors ) );
        }
        if ( !empty( $comment ) ) {
            $newShift->activityComments = $comment;
        }
        $newShift->timeStarted = DateTimeUtility::roundTime( date( 'H:i:s' ) );

        $unfinishedShift = $this->shifts->getUnfinishedShifts()->getOne();
        if ( $unfinishedShift !== null ) {
            if ( $unfinishedShift->job->id === $newShift->job->id
                && $unfinishedShift->activity->id === $newShift->activity->id
                && $unfinishedShift->furniture->id === $newShift->furniture->id
                && $unfinishedShift->date === $newShift->date ) {
                return $this->addError( 'No point starting a new shift as it is identical to the current shift.' );
            }
            if ( !$this->finishCurrentShift() ) {
                return false;
            }
        }


        if ( $newShift->save() ) {
            $newShift->activity = (new ActivityFactory( $this->db, $this->messages ))->getEntity( $activityID );
            $newShift->job = (new JobFactory( $this->db, $this->messages ))->getJob( $jobID );
            $newShift->furniture = $newShift->job->furniture[$furnitureID] ?? null;
            $this->shifts->addOrReplaceShift( $newShift );
            return $newShift;
        }

        return false;
    }

    /**
     * @return string
     */
    public function secondOrThirdPerson(): string
    {
        return $this->name ?? $this->entityName;
    }

    /**
     * @param array $errors
     * @return string
     */
    public function healthCheck(array $errors = []): string
    {
        $currentShifts = $this->shifts->getUnfinishedShifts()->getAll();

        $userString = $this->secondOrThirdPerson();
        $verbString = $userString === 'you' ? 'have' : 'has';

        if ( empty( $this->name ) ) {
            $errors[] = ucwords( $userString ) . ' should have a name as part of user profile.';
        }
        if ( empty( $this->pin ) ) {
            $errors[] = ucwords( $userString ) . ' should have a pin number as part of user profile.';
        }

        if ( $this->role === 'staff' && empty( $this->rate ) ) {
            $errors[] = ucwords( $userString ) . ' should have a rate greater than $0.00 per hour.';
        }

        $numberOfShifts = count( $currentShifts );
        if ( $numberOfShifts > 1 ) {
            $errors[] = ucfirst( $userString ) . ' cannot start or finish shifts because '
                . $userString . ' '
                . $verbString . ' '
                . $numberOfShifts
                . ' unfinished shifts. Each worker should have only one active shift at a time. An admin must set a finish time for the extra shifts manually.';
        } elseif ( $numberOfShifts === 1 ) {
            $today = date( 'Y-m-d' );
            foreach ( $currentShifts as $shift ) {
                if ( $shift->date !== $today ) {
                    $errors[] = ucfirst( $userString ) . ' '
                        . $verbString
                        . ' an unfinished shift started on a day other than today.<br>An admin must set a finish time for shift ID: <strong>'
                        . $shift->id
                        . '</strong>, date <strong>'
                        . $shift->date
                        . '</strong> manually.';
                }
            }
        }
        return parent::healthCheck( $errors );
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function finishCurrentShift(): bool
    {
        if ( !empty( $this->healthCheck() ) ) {
            return false;
        }
        $unfinishedShifts = $this->shifts->getUnfinishedShifts();
        if ( $unfinishedShifts->getCount() === 0 ) {
            return true;
        }
        $unfinishedShift = $unfinishedShifts->getOne();

        //$newShift

        $success = $unfinishedShift->finishShift();
        $this->shifts->addOrReplaceShift( $unfinishedShift );
        return $success;
    }

    /**
     * @return bool
     */
    public function hadLunchToday(): bool
    {
        foreach ( $this->shifts->getShiftsToday()->getAll() as $shift ) {
            if ( $shift->activity->id === 0 || $shift->activity->name === 'Lunch' ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function canDelete(): bool
    {
        $numberOfShifts = $this->shifts->getCount();
        if ( $numberOfShifts > 0 ) {
            return $this->addError( ucfirst( $this->name ) . ' has <strong>' . $numberOfShifts . '</strong> associated shifts. You cannot delete ' . ucfirst( $this->name ) . ' until the related shifts are deleted.' );
        }
        return true;
    }
}