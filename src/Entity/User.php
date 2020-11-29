<?php

namespace Phoenix\Entity;

use Phoenix\URL;
use Phoenix\Utility\DateTimeUtility;
use Phoenix\Messages;
use Phoenix\PDOWrap;
use Phoenix\Roles;
use Phoenix\Utility\HTMLTags;
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
     * For setting Entity properties related to DB table columns
     *
     * @param $property
     * @param $value
     */
    public function setProperty(string $property = '', $value = null): void
    {
        if ( $property === 'unencrypted-password' && !empty( $value ) ) { //Changing password
            $property = 'password';
            $value = password_hash( $value, PASSWORD_BCRYPT, $this->cryptoOptions );
        }

        parent::setProperty( $property, $value );
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
    public function getFirstName(): string
    {
        $name = strtok( $this->name, ' ' );
        return empty( $name ) ? $this->entityName : $name;
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
            $errors[] = 'Job ' . HTMLTags::getBadgeHTML( 'ID: ' . $jobID, 'danger' ) . " doesn't exist.";
        }

        $newShift->activity = (new ActivityFactory( $this->db, $this->messages ))->getEntity( $activityID );
        if ( $newShift->activity->id === null ) {
            $errors[] = 'Activity' . HTMLTags::getBadgeHTML( 'ID: ' . $activityID, 'danger' ) . " doesn't exist.";
        }

        if ( $furnitureID !== null && $furnitureID !== '' ) {
            $newShift->furniture = (new FurnitureFactory( $this->db, $this->messages ))->getEntity( $furnitureID );
            if ( $newShift->furniture->id === null ) {
                $errors[] = 'Furniture' . $this->getIDBadge( $furnitureID, 'danger' ) . " doesn't exist.";
            }
        }
        if ( !empty( $errors ) ) {
            return $this->addError( '<h5 class="alert-heading">Can\'t ' . $newShift->getActionString( 'present', 'start' ) . ':</h5>' . HTMLTags::getListGroup( $errors ) );
        }
        if ( !empty( $comment ) ) {
            $newShift->activityComments = $comment;
        }
        $newShift->timeStarted = DateTimeUtility::roundTime();

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
            $this->shifts->addOrReplace( $newShift );
            return $newShift;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getWorkerWeekLink(): string
    {
        return (new URL())
            ->setQueryArgs( [
                'page' => 'report',
                'report' => 'worker_week',
                'user' => $this->id
            ] )
            ->write();
    }

    /**
     * @return string
     */
    public function secondOrThirdPerson(): string
    {
        return $this->name ?? $this->entityName;
    }

    /**
     * @return array
     */
    public function doHealthCheck(): array
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

        if ( empty( $this->rate ) && $this->shifts->getCount() > 0 ) {
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
                    $date = !empty( $shift->date ) ? ' date ' . HTMLTags::getBadgeHTML( date( 'd-m-Y', strtotime( $shift->date ) ), 'primary' ) : '';
                    $errors[] = ucfirst( $userString ) . ' '
                        . $verbString
                        . ' an unfinished shift started on a day other than today.<br>An admin must manually set a finish time for shift'
                        . $shift->getIDBadge( null, 'primary' ) . $date . '.';
                }
            }
        }
        return $errors ?? [];
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
        $unfinishedShift->worker = $this; //add user so Shift->healthCheck can confirm user role is staff

        $success = $unfinishedShift->finishShift();
        $this->shifts->addOrReplace( $unfinishedShift );
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
            return $this->addError( ucfirst( $this->getFirstName() ) . ' has <strong>' . $numberOfShifts . '</strong> associated shifts. You cannot delete ' .  $this->getFirstName()  . ' until the related shifts are deleted.' );
        }
        return true;
    }
}