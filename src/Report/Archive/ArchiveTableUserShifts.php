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
            'title' => 'Time Started'
        ],
        'time_finished' => [
            'title' => 'Time Finished',
        ],
        'hours' => [
            'title' => 'Hours',
            'format' => 'hoursminutes'
        ],
        'activity' => [
            'title' => 'Activity'
        ],
    ];

    /**
     * @param Shift $shift
     * @return array
     */
    public function extractEntityData($shift): array
    {
        return [
            'job' => $shift->job->id,
            'date' => $shift->date,
            'time_started' => $shift->timeStarted ?? '-',
            'time_finished' => $shift->timeFinished ?? '-',
            'hours' => $shift->getShiftLength(),
            'activity' => $shift->activity->displayName ?? 'Unknown Activity'
        ];
    }
}