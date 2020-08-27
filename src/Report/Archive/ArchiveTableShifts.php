<?php


namespace Phoenix\Report\Archive;


use Phoenix\Entity\Shift;

/**
 * Class ArchiveTableJobs
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableShifts extends ArchiveTable
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
            'title' => 'Activity'
        ]
    ];


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
            'worker' => $this->htmlUtility::getButton( [
                    'element' => 'a',
                    'content' => $shift->worker->name,
                    'href' => $shift->worker->getLink(),
                    'class' => 'text-white'
                ] ) ?? $shift->worker->name,
            'date' => $shift->date,
            'time_started' => $shift->timeStarted ?? '-',
            'time_finished' => $shift->timeFinished ?? '-',
            'minutes' => $minutes,
            'hours' => $minutes,
            'activity' => $shift->activity->displayName ?? '-',
        ];
    }
}