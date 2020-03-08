<?php


namespace Phoenix;

/**
 * @property integer|Activity $activity
 * @property integer $activityComments
 * @property string $date
 * @property integer|Furniture $furniture
 * @property integer|Job $job
 * @property string $timeStarted
 * @property string $timeFinished
 * @property integer|User $worker
 *
 * Class Shift
 *
 * @package Phoenix
 */
class Shift extends Entity
{
    /**
     * @var integer
     */
    protected $_activity;

    /**
     * @var integer
     */
    protected $_activityComments;

    /**
     * @var string
     */
    protected $_date;

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
    protected $_timeStarted;

    /**
     * @var string
     */
    protected $_timeFinished;

    /**
     * @var integer
     */
    protected $_worker;

    /**
     * @var string
     */
    protected $_tableName = 'shifts';

    /**
     * Return shift length in minutes
     *
     * @return int
     */
    public function getShiftLength(): int
    {
        if ( empty( $this->timeStarted ) || empty( $this->timeFinished ) ) {
            return 0;
        }
        return DateTime::timeDifference( $this->timeStarted, $this->timeFinished );
    }

    /**
     * @return string
     */
    public function getFurnitureString(): string
    {
        if ( empty( $this->furniture ) ) {
            if ( $this->job === 0 || $this->job->id === 0 ) {
                return 'N/A - Factory Job';
            }
            return 'Unknown';
        }
        if ( $this->furniture instanceof Furniture ) {
            $furnitureString = $this->furniture->getFurnitureString();
        }

        return $furnitureString ?? 'Unknown';
    }

    /**
     * @return bool
     */
    public function finishShift(): bool
    {
        if ( !empty( $this->timeFinished ) ) {
            return false;
        }

        $currentTimeString = DateTime::roundTime( date( 'H:i:s' ) ); //get current time
        $cutOffTimeString = '17:00:00'; //5pm
        $cutOffTime = strtotime( $cutOffTimeString );
        if ( strtotime( $this->timeStarted ) > $cutOffTime ) {
            //If shift started after cutoff time set it's finish time to start time so it has 0 minutes length. Otherwise we have shifts starting after cutoff and finishing at cutoff making for negative shift length.
            $finishTime = $this->timeStarted;
        } else {
            $finishTime = strtotime( $currentTimeString ) < $cutOffTime ? $currentTimeString : $cutOffTimeString;
        }
        $minutes = DateTime::timeDifference( $this->timeStarted, $finishTime );
        return $this->db->update( 'shifts',
            [
                'time_finished' => $finishTime,
                'minutes' => $minutes
            ],
            ['ID' => $this->id]
        );
    }

    /**
     * @param int|Activity $activity
     * @return int|Activity
     */
    protected function activity($activity = 0)
    {
        if ( !empty( $activity ) ) {
            //$activity = is_string($activity) ? intval($activity) : '';
            $this->_activity = $activity;
        }
        return $this->_activity ?? 0;
    }

    /**
     * @param string $activityComments
     * @return string
     */
    protected function activityComments(string $activityComments = ''): string
    {
        if ( !empty( $activityComments ) ) {
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
     * @param int|Furniture $furniture
     * @return int|Furniture
     */
    protected function furniture($furniture = 0)
    {
        if ( !empty( $furniture ) ) {
            $this->_furniture = $furniture;
        }
        return $this->_furniture ?? 0;
    }

    /**
     * @param int|Job $job
     * @return int|Job
     */
    protected function job($job = 0)
    {
        if ( !empty( $job ) ) {
            $this->_job = $job;
        }
        return $this->_job ?? 0;
    }

    /**
     * @param string $timeStarted
     * @return string
     */
    protected function timeStarted(string $timeStarted = ''): string
    {
        if ( !empty( $timeStarted ) ) {
            $this->_timeStarted = $timeStarted;
        }
        return $this->_timeStarted ?? '';
    }

    /**
     * @param string $timeFinished
     * @return string
     */
    protected function timeFinished(string $timeFinished = ''): string
    {
        if ( !empty( $timeFinished ) ) {
            $this->_timeFinished = $timeFinished;
        }
        return $this->_timeFinished ?? '';
    }

    /**
     * @param int|User $worker
     * @return int|User
     */
    protected function worker($worker = 0)
    {
        if ( !empty( $worker ) ) {
            $this->_worker = $worker;
        }
        return $this->_worker ?? 0;
    }


    function update()
    {

    }
}