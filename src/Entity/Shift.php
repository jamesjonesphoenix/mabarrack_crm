<?php


namespace Phoenix\Entity;

use DateTime;
use Phoenix\Utility\DateTimeUtility;
use Phoenix\Utility\HTMLTags;

/**
 * @property integer|Activity  $activity
 * @property string            $activityComments
 * @property string            $date
 * @property integer|Furniture $furniture
 * @property integer|Job       $job
 * @property string            $timeStarted
 * @property string|null       $timeFinished
 * @property integer|User      $employee
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
    protected $_employee;

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
        'employee' => [
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
        return $this->shiftCost = $this->getShiftLength() * $this->employee->rate / 60;
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
     * Designed for employees clocking off a shift
     *
     * @return bool
     * @throws \Exception
     */
    public function finishShift(): bool
    {
        $shiftName = $this->isLunch();
        $errorString = "Can't finish " . $shiftName . $this->getIDBadge( null, 'danger' ) . '. ';
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

        $currentTime = DateTimeUtility::roundTime(); //get current time
        $cutoffTime = (new SettingFactory( $this->db, $this->messages ))->getCutoffTime();
        $currentDate = date( 'd-m-Y' );

        if ( DateTimeUtility::isBefore( $this->date, $currentDate, false ) ) {
            $this->timeFinished = $cutoffTime;
            $this->messages->add(
                'Shift with date '
                . HTMLTags::getBadgeHTML( $this->date, 'primary' )
                . ' clocked off on '
                . HTMLTags::getBadgeHTML( $currentDate, 'primary' )
                . ' so finish time was automatically set to cutoff time of '
                . HTMLTags::getBadgeHTML( $cutoffTime, 'primary' )
                . '. An admin should probably double check this shift.'
            );
            $this->appendComment( 'Shift with date ' . $this->date . ' clocked off on ' . $currentDate . ' so finish time was automatically set to cutoff time of ' . $cutoffTime . '.' );
            $previousDayShift = true;
        }

        if ( DateTimeUtility::isAfter( $this->timeStarted, $cutoffTime, true ) ) {
            /*
             * If shift started after cutoff time set its finish time equal to start time so it has 0 minutes length.
             * Otherwise we have shifts starting after cutoff and finishing at cutoff making for negative shift length.
            */
            $this->timeFinished = (new DateTime( $this->timeStarted ))->modify( '+1 minutes' )->format( 'H:i' );
            $this->messages->add(
                'Shift '
                . $this->getIDBadge( null, 'primary' )
                . ' started after cutoff time '
                . HTMLTags::getBadgeHTML( $cutoffTime, 'primary' )
                . ' so finish time was set to be 1 minute after the start time. An admin should probably double check this shift.',
                'warning'
            );

            $this->appendComment( 'Shift started after cutoff time of ' . $cutoffTime . ' so finish time was automatically set to 1 minute after the start time.' );

        } elseif (empty($previousDayShift)) {
            if ( (empty( $cutoffTime ) || DateTimeUtility::isBefore( $currentTime, $cutoffTime )) ) { // Finished before cut off time, therefore legit
                $this->timeFinished = $currentTime;
            } else { //Finished after cut off time
                $this->timeFinished = $cutoffTime;
                $this->messages->add(
                    'Shift ' . $this->getIDBadge( null, 'primary' ) . ' finish time was moved from '
                    . HTMLTags::getBadgeHTML( $currentTime, 'primary' )
                    . ' to the cutoff time '
                    . HTMLTags::getBadgeHTML( $cutoffTime, 'primary' ) . '.',
                    'info'
                );
                $this->appendComment( 'Shift finish time was automatically moved from ' . $currentTime . ' to the cutoff time ' . $cutoffTime . '.' );
            }
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
     * @param string $comment
     * @return bool
     */
    public function appendComment(string $comment = 'null'): bool
    {
        $existing = $this->activityComments;
        if ( empty( $existing ) ) {
            $this->activityComments = $comment;
        } else {
            $this->activityComments = $existing . PHP_EOL . $comment;
        }
        return true;
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
            if ( !$job instanceof Job ) {
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
     * @param int|User|null $employee
     * @return User
     */
    protected function employee($employee = null)
    {
        if ( $employee !== null ) {
            if ( !$employee instanceof User ) {
                $employeeID = $employee;
                $employee = new User();
                $employee->id = $employeeID;
            }
            $this->_employee = $employee;
        }
        return $this->_employee ?? new User();
    }

    /**
     * @param string $tense
     * @param string $action
     * @return string
     */
    protected function getActionString($tense = 'present', string $action = ''): string
    {
        if ( CurrentUser::instance()->id !== $this->employee->id ) {
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
     * @return array
     */
    public
    function doHealthCheck(): array
    {
        $shiftName = ucfirst( $this->isLunch() );
        $timeStarted = $this->timeStarted;
        $timeFinished = $this->timeFinished;

        if ( empty( $timeStarted ) ) {
            $errors[] = $shiftName . " <strong>start time</strong> hasn't been set. Please set a <strong>start time</strong>.";
        }
        if ( !empty( $timeFinished ) ) {

            if ( DateTimeUtility::isAfter( $timeStarted, $timeFinished, false ) ) {
                $errors[] = $shiftName . ' <strong>finish time</strong> cannot be earlier than the <strong>start time</strong>.';
            }
            if ( $timeStarted === $timeFinished ) {
                $errors[] = $shiftName . " <strong>finish time</strong> shouldn't be exactly the same as <strong>start time</strong>.";
            }
        }
        $activity = $this->activity;
        if ( $activity->id === null ) {
            $errors[] = 'Shift has no <strong>activity</strong> assigned.';
        }
        if ( $this->job->id !== 0 ) {
            if ( empty( $this->job->furniture ) ) {
                $errors[] = 'Job' . $this->job->getIDBadge( null, 'danger' ) . ' has no furniture assigned for this shift to be assigned to.';
            } else {
                if ( $this->furniture->id === null ) {
                    $errors[] = '<p>Shift has no furniture assigned to it.</p>';
                } else {
                    if ( empty( $this->furniture->name ) ) {
                        $shiftName = $this->furniture->name;
                        $errors[] = '<p>Shift' . $this->getIDBadge( null, 'primary' ) . ' is assigned with furniture ' . $this->furniture->getIDBadge( null, 'danger' ) . ' but unknown furniture name. Does this furniture exist?</p>';
                    }
                    if ( !array_key_exists( $this->furniture->id, $this->job->furniture ?? [] ) ) {
                        $furnitureString = !empty( $this->furniture->name ) ? HTMLTags::getBadgeHTML( $this->furniture->name, 'primary' ) : $this->furniture->getIDBadge( null, 'primary' );
                        $errors[] = 'Shift is assigned furniture ' . $furnitureString . ' which is not part of job ' . $this->job->getIDBadge( null, 'primary' ) . '.';
                    }
                }
            }
            if ( $activity->factoryOnly ) {
                $errors[] = 'You cannot book ' . HTMLTags::getBadgeHTML( $activity->displayName, 'primary' ) . ' for job ' . $this->job->getIDBadge( null, 'primary' ) . ' because it is a factory only activity. Please choose a billable activity or select the factory job.';
            }
        } else {
            if ( $activity->factoryOnly === false ) {
                $errors[] = 'You cannot book ' . HTMLTags::getBadgeHTML( $activity->displayName, 'primary' ) . ' for factory work because it is a billable activity. Please choose a factory only activity or a billable job.';
            }
            if ( $this->furniture->id !== null ) {
                $errors[] = $shiftName . ' should not have furniture assigned because it is assigned to non-billable job.';
            }
        }

        if ( $this->employee->id === null ) {
            $errors[] = $shiftName . ' has no employee assigned.';
        }
        return $errors ?? [];
    }

    /**
     * Get DB input array
     * Make sure new shift has furniture because user can't choose it because the job won't have been chosen yet
     *
     * @return array
     */
    protected function getSaveData(): array
    {
        $data = parent::getSaveData();
        if ( $this->exists || !empty( $this->checkRequiredColumns( $data ) ) || !empty( $data['furniture'] ) || $data['job'] === 0 ) {
            return $data;
        }
        $jobFurniture = $this->job->furniture;
        if ( count( $jobFurniture ) === 0 ) {
            $this->addError( 'Job has no assigned furniture for shift to book to.' );
            return $data;
        }
        $defaultFurnitureID = current( $jobFurniture )->id ?? null;
        // $this->addError($defaultFurnitureID);
        $this->furniture = (new FurnitureFactory( $this->db, $this->messages ))->getEntity( $defaultFurnitureID, false );

        $data['furniture'] = $this->furniture->id ?? null;


        if ( count( $jobFurniture ) > 1 ) {
            foreach ( $this->job->furniture as $furniture ) {
                $furnitureForString[] = $furniture->name;
            }
            $multipleFurnitureString = ' Please edit shift furniture if this assumption is incorrect. Job includes furniture ' . implode( ' ', $furnitureForString ?? [] ) . '.';
        } else {
            $multipleFurnitureString = ' This is the only furniture type assigned to job ' . $this->job->getIDBadge( null, 'primary' );
        }


        if ( $data['furniture'] !== null ) {
            $this->messages->add( 'Assigned furniture' . HTMLTags::getBadgeHTML( $this->furniture->name, 'primary' ) . ' to new shift. ' . $multipleFurnitureString );
        } else {
            $this->addError( '<p>Job' . $this->job->getIDBadge( null, 'danger' ) . ' is assigned furniture'
                . $this->getIDBadge( $defaultFurnitureID, 'danger' )
                . ' that does not exist in the db. The job must have valid furniture assigned before shifts can be booked to it.</p>'
                . HTMLTags::getViewButton( $this->job->getLink(), 'View Job' )
            );
        }

        return $data;
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
                'href' => $archivePage . '&limit=1000',
                'content' => 'Latest 1000 Shifts'
            ],
            'all' => [
                'href' => $archivePage,
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