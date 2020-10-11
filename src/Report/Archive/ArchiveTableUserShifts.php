<?php


namespace Phoenix\Report\Archive;


use Phoenix\Entity\Shift;

/**
 * Class ArchiveTableUserShifts
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableUserShifts extends ArchiveTable
{
    /**
     * @var array
     */
    protected array $columns = [
        'job' => [
            'title' => 'Job',
        ],
        'date' => [
            'title' => 'Date',
            'format' => 'date'
        ],
        'time_started' => [
            'title' => 'Time Started',
            'default' => '&minus;'
        ],
        'time_finished' => [
            'title' => 'Time Finished',
            'default' => '&minus;'
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
            'default' => 'Unknown Activity'
        ]
    ];

    /**
     * @var bool
     */
    protected bool $printButton = false;

    /**
     * @param Shift $shift
     * @return array
     */
    public function extractEntityData($shift): array
    {
        $minutes = $shift->getShiftLength();
        if ( $minutes === 0 && empty( $shift->timeFinished ) && !empty( $shift->timeStarted ) ) {
            $minutes = 'N/A';
        }
        return [
            'job' => $shift->job->id === 0 ? 'Factory' : $shift->job->id,
            'date' => $shift->date,
            'time_started' => $shift->timeStarted,
            'time_finished' => $shift->timeFinished,
            'minutes' => $minutes,
            'hours' => $minutes,
            'activity' => $shift->activity->displayName,
        ];
    }
}