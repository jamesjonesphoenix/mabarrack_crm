<?php


namespace Phoenix\Report\Archive;


use DateTime;
use Phoenix\Entity\Shift;

/**
 * Class ArchiveTableJobShifts
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableJobShifts extends ArchiveTable
{
    /**
     * @var array
     */
    protected array $columns = [
        'worker' => [
            'title' => 'Worker',
        ],
        'date' => [
            'title' => 'Date',
            'format' => 'date'
        ],
        'week_ending' => [
            'title' => 'Week Ending',
            'format' => 'date'
        ],
        'time_started' => [
            'title' => 'Time Started'
        ],
        'time_finished' => [
            'title' => 'Time Finished',
        ],
        'minutes' => [
            'title' => 'Minutes',
            'format' => 'number',
            'hidden' => true
        ],
        'hours' => [
            'title' => 'Hours',
            'format' => 'hoursminutes'
        ],
        'activity' => [
            'title' => 'Activity',
            'default' => '&minus;'
        ],
        'rate' => [
            'title' => 'Rate',
            'format' => 'currency'
        ],
        'line_item_cost' => [
            'title' => 'Line Item Cost',
            'format' => 'currency'
        ]
    ];

    /**
     * @var array
     */
    private array $dateObjects = [];

    /**
     * @var string
     */
    protected string $title = 'Job Shifts';

    /**
     * @param Shift $shift
     * @return array
     * @throws \Exception
     */
    public function extractEntityData($shift): array
    {
        if ( empty( $this->dateObjects[$shift->date] ) ) {
            $this->dateObjects[$shift->date] = new DateTime( $shift->date ); //Create a new DateTime object
            $this->dateObjects[$shift->date]->modify( 'next thursday' ); //Modify the date it contains
        }
        $minutes = $shift->getShiftLength();
        if ( $minutes === 0 && empty( $shift->timeFinished ) && !empty( $shift->timeStarted ) ) {
            $minutes = 'N/A';
        }
        return [
            'worker' => $shift->worker->name,
            'date' => $shift->date,
            'week_ending' => $this->dateObjects[$shift->date]->format( 'd-m-Y' ),
            'time_started' => $shift->timeStarted,
            'time_finished' => $shift->timeFinished,
            'minutes' => $minutes,
            'hours' => $minutes,
            'activity' => $shift->activity->displayName,
            'rate' => $shift->worker->rate,
            'line_item_cost' => $shift->getShiftCost(),
        ];

    }


}