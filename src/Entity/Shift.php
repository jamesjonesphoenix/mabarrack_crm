<?php


namespace Phoenix\Entity;

use Phoenix\DateTimeUtility;

/**
 * @property integer|Activity  $activity
 * @property string            $activityComments
 * @property string            $date
 * @property integer|Furniture $furniture
 * @property integer|Job       $job
 * @property string            $timeStarted
 * @property string|null       $timeFinished
 * @property integer|User      $worker
 *
 * Class Shift
 *
 * @package Phoenix
 */
class Shift extends Entity
{
    /**
     * @var string Fontawesome icon
     */
    protected string $icon = 'stopwatch';

    /**
     * @var int|Activity
     */
    protected $_activity;

    /**
     * @var string
     */
    protected string $_activityComments;

    /**
     * @var string
     */
    protected string $_date;

    /**
     * @var integer|Furniture
     */
    protected $_furniture;

    /**
     * @var integer|Job
     */
    protected $_job;

    /**
     * @var string
     */
    protected string $_timeStarted;

    /**
     * @var ?string
     */
    protected ?string $_timeFinished;

    /**
     * @var integer|User
     */
    protected $_worker;

    /**
     * Database columns map. Don't need to include ID column in this array.
     *
     * @var array
     */
    protected array $_columns = [
        'job' => [
            'type' => 'id',
            'required' => true
        ],
        'furniture' => [
            'type' => 'id',
        ],
        'worker' => [
            'type' => 'id',
            'required' => true
        ],
        'date' => [
            'type' => 'date',
            'required' => true
        ],
        'time_started' => [
            'type' => 'time',
            'required' => true
        ],
        'time_finished' => [
            'type' => 'time',
        ],
        'activity' => [
            'type' => 'id',
            'required' => true
        ],
        'activity_values' => [
            'type' => 'string',
        ],
        'activity_comments' => [
            'type' => 'string',
        ]
    ];

    /**
     * @var string
     */
    private string $cutOffTime = '17:00'; //5pm

    /**
     * @var int
     */
    private int $shiftLength;

    /**
     * @var int
     */
    private int $shiftCost;

    /**
     * Return shift length in minutes
     *
     * @return int
     */
    public function getShiftLength(): int
    {
        if ( !empty( $this->shiftLength ) ) {
            return $this->shiftLength;
        }
        if ( empty( $this->timeStarted ) || empty( $this->timeFinished ) ) {
            return 0;
        }
        return $this->shiftLength = DateTimeUtility::timeDifference( $this->timeStarted, $this->timeFinished );
    }


    /**
     * Returns shift cost in dollars and cents
     *
     * @return float
     */
    public function getShiftCost(): float
    {
        if ( !empty( $this->shiftCost ) ) {
            return $this->shiftCost;
        }
        return $this->shiftCost = $this->getShiftLength() * $this->worker->rate / 60;
    }

    /**
     * @return string
     */
    public function isLunch(): string
    {
        if ( $this->activity === null ) {
            return 'shift';
        }
        if ( is_int( $this->activity ) ) {
            $activityName = (new ActivityFactory( $this->db, $this->messages ))->getEntity( $this->activity )->name;
        } else {
            $activityName = $this->activity->name;
        }
        //$shiftName = $shiftName === 'lunch' ? 'lunch' : 'shift';
        return $activityName === 'Lunch' ? 'lunch' : 'shift';
    }

    /**
     * @return string
     */
    public function getFurnitureString(): string
    {
        //d($this->furniture );
        if ( $this->furniture->id === null ) {

            if ( $this->job->id === 0 ) {
                return 'N/A';
            }
            return 'Unknown';
        }
        return $this->furniture->getFurnitureString() ?? 'Unknown';
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function startShift(): bool
    {
        if ( !empty( $this->timeStarted ) ) {
            return $this->addError( "Can't start shift. Looks like this shift (id=" . $this->id . ') has already started.' );
        }
        $result = $this->save();

        if ( $result ) {
            if ( $this->isLunch() ) {
                $this->messages->add( 'Lunch started.' );
            } else {
                $this->messages->add( 'Shift started.' );
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    protected function getCreationString(): string
    {
        return 'Created';
    }

    /**
     * Designed for workers clocking off a shift
     *
     * @return bool
     * @throws \Exception
     */
    public function finishShift(): bool
    {
        $shiftName = $this->isLunch();
        $idString = !empty( $this->id ) ? ' <span class="badge badge-primary">ID: ' . $this->id . '</span>'  : '';
        $errorString = "Can't finish " . $shiftName . $idString . '. ';
        $shiftData = $this->getDataHTMLTable();
        if ( empty( $this->exists ) ) {
            return $this->addError( $errorString . 'Apparently this ' . $shiftName . " doesn't exist in the database." . $shiftData );
        }
        if ( !empty( $this->timeFinished ) ) {
            return $this->addError( $errorString . 'Apparently this ' . $shiftName . ' has already finished.' . $shiftData );
        }
        if ( empty( $this->timeStarted ) ) {
            return $this->addError( $errorString . 'Apparently this ' . $shiftName . " hasn't been started yet." . $shiftData );
        }

        $currentTime = DateTimeUtility::roundTime( date( 'H:i' ) ); //get current time
        $cutOffTime = $this->cutOffTime;


        if ( DateTimeUtility::timeDifference( $cutOffTime, $this->timeStarted ) > 0 ) { //If shift started after cutoff time set it's finish time equal to start time so it has 0 minutes length. Otherwise we have shifts starting after cutoff and finishing at cutoff making for negative shift length.
            $this->timeFinished = $this->timeStarted;
        } elseif ( DateTimeUtility::timeDifference( $currentTime, $cutOffTime ) > 0 ) { //Finished before cut off time
            $this->timeFinished = $currentTime;
        } else { //Finished after cut off time
            $this->timeFinished = $cutOffTime;
        }
        return $this->save();
    }

    /**
     * @param int|Activity|null $activity
     * @return int|Activity
     */
    protected function activity($activity = null)
    {
        if ( $activity !== null ) {
            if ( is_int( $activity ) ) {
                $activityID = $activity;
                $activity = new Activity();
                $activity->id = $activityID;
            }
            $this->_activity = $activity;
        }
        return $this->_activity ?? new Activity();
    }

    /**
     * @param string|null $activityComments
     * @return string
     */
    protected function activityComments(string $activityComments = null): string
    {
        if ( $activityComments !== null ) {
            $this->_activityComments = $activityComments;
        }
        return $this->_activityComments ?? '';
    }

    /**
     * @param string $date
     * @return string
     */
    protected function date(string $date = ''): string
    {
        if ( !empty( $date ) ) {
            $this->_date = $date;
        }
        return $this->_date ?? '';
    }

    /**
     * @param int|Furniture|null $furniture
     * @return Furniture
     */
    protected function furniture($furniture = null)
    {
        if ( !empty( $furniture ) ) {
            if ( is_int( $furniture ) ) {
                $furnitureID = $furniture;
                $furniture = new Furniture();
                $furniture->id = $furnitureID;
            }
            $this->_furniture = $furniture;
        }
        return $this->_furniture ?? new Furniture();
    }

    /**
     * @param int|Job|null $job
     * @return Job
     */
    protected function job($job = null)
    {
        if ( $job !== null ) {
            if ( is_int( $job ) ) {
                $jobID = $job;
                $job = new Job();
                $job->id = $jobID;
            }
            $this->_job = $job;
        }
        return $this->_job ?? new Job();
    }

    /**
     * @param ?string $timeStarted
     * @return string
     */
    protected function timeStarted(string $timeStarted = null): string
    {
        if ( is_string( $timeStarted ) ) {
            return $this->_timeStarted = $timeStarted;
        }
        return $this->_timeStarted ?? '';
    }

    /**
     * @param string|null $timeFinished
     * @return string
     */
    protected function timeFinished(string $timeFinished = null): ?string
    {
        if ( is_string( $timeFinished ) ) {
            if ( empty( $timeFinished ) ) {
                return $this->_timeFinished = null;
            }
            return $this->_timeFinished = $timeFinished;
        }
        return $this->_timeFinished ?? null;
    }

    /**
     * @param int|User|null $worker
     * @return User
     */
    protected function worker($worker = null)
    {
        if ( $worker !== null ) {
            if ( is_int( $worker ) ) {
                $workerID = $worker;
                $worker = new User();
                $worker->id = $workerID;
            }
            $this->_worker = $worker;
        }
        return $this->_worker ?? new User();
    }

    /**
     * @param string $tense
     * @param string $action
     * @return string
     */
    protected function getActionString($tense = 'present', string $action = ''): string
    {
        if ( CurrentUser::instance()->id !== $this->worker->id ) {
            return parent::getActionString( $tense, $action );
        }
        if ( $action === 'update'
            && !empty( $this->timeFinished )
            && $this->date === date( 'Y-m-d' ) ) { //probably finishing a shift
            if ( $tense === 'past' ) {
                $string = 'finished ';
            } else {
                $string = 'finish';
            }
        } elseif ( $action === 'start' || ($action === 'create'
                && empty( $this->timeFinished )) ) { //probably starting a new shift
            if ( $tense === 'past' ) {
                $string = 'started new';
            } else {
                $string = 'start new';
            }
        }
        if ( empty( $string ) ) {
            return parent::getActionString( $tense, $action );
        }
        return $string . ' ' . $this->isLunch();
    }


    /**
     * @param array $errors
     * @return string
     */
    public
    function healthCheck(array $errors = []): string
    {
        $shiftName = ucfirst( $this->isLunch() );
        $timeStarted = $this->timeStarted;
        $timeFinished = $this->timeFinished;

        if ( !empty( $timeFinished ) ) {
            if ( empty( $timeStarted ) ) {
                $errors[] = $shiftName . " <strong>finish time</strong> cannot be set because <strong>start time</strong> hasn't yet been set. Please set a <strong>start time</strong>.";
            }
            if ( DateTimeUtility::timeDifference( $timeStarted, $timeFinished ) < 0 ) {
                $errors[] = $shiftName . ' <strong>finish time</strong> cannot be earlier than the <strong>start time</strong>.';
            }
            if ( $timeStarted === $timeFinished ) {
                $errors[] = $shiftName . " <strong>finish time</strong> shouldn't be exactly the same as <strong>start time</strong>.";
            }
        }

        $activity = $this->activity;
        if ( $activity->id === null ) {
            $errors[] = 'Shift has no <strong>activity</strong> assigned.';
        } else {
            if ( !$activity->exists ) {
                $activity = (new ActivityFactory( $this->db, $this->messages ))->getEntity( $activity->id );
            }

            if ( !$activity->factoryOnly && $this->job->id === 0 ) {
                $errors[] = 'You cannot book <strong>' . $activity->displayName . '</strong> for the factory job because it is a billable activity. Please choose a factory only activity or a billable job.';
            }
            if ( $activity->factoryOnly && $this->job->id !== 0 ) {
                $errors[] = 'You cannot book <strong>' . $activity->displayName . '</strong> for a billable job because it is a factory only activity. Please choose a billable activity or the factory job.';
            }
        }

        //$furnitureID = $entity->furniture->id ?? null;
        if ( $this->furniture->id === null ) {
            if ( $this->job->id !== 0 ) {
                $errors[] = '<p>Shift has no furniture assigned to it. Shift is not part of a factory job so it should have furniture assigned.</p>';
            }
        } elseif ( empty( $this->furniture->name ) ) {
            //$dummyFurniture = new Furniture();
            //$dummyFurniture->id = $furnitureID;
            $errors[] = '<p>Shift <span class="badge badge-primary">ID: ' . $this->id . '</span> is assigned with furniture <span class="badge badge-primary">ID: ' . $this->furniture->id . '</span> but unknown furniture name. Does this furniture exist?</p>';
        }
        if ( empty( $this->job->furniture ) ) {
            $errors[] = 'Job <span class="badge badge-primary">ID: ' . $this->job->id . '</span> has no furniture assigned for this shift to be assigned to.';
        }
        if ( !array_key_exists( $this->furniture->id, $this->job->furniture ?? [] ) ) {
            $errors[] = 'Shift is assigned furniture <strong>' . $this->furniture->name . '</strong> which is not part of the assigned job <span class="badge badge-primary">ID: ' . $this->job->id . '</span>';
        }


        return parent::healthCheck( $errors );
    }

    /**
     * @return array
     */
    public
    function getCustomNavItems(): array
    {
        $archivePage = $this->getArchiveLink() . '&order_by=date';
        return [
            'last_1000' => [
                'url' => $archivePage . '&limit=1000',
                'text' => 'Latest 1000 Shifts'
            ],
            'all' => [
                'url' => $archivePage,
            ]
        ];
    }

    /**
     * @return string
     */
    public
    function getArchiveLink(): string
    {
        return parent::getArchiveLink() . '&order_by=date';
    }
}