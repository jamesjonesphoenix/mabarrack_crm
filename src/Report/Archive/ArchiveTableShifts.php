<?php

namespace Phoenix\Report\Archive;

use DateTime;
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
        'employee' => [
            'title' => 'Employee',
            'default' => '&minus;'
        ],
        'job' => [
            'title' => 'Job',
        ],
        'furniture' => [
            'title' => 'Furniture',
            'default' => '&minus;',
            'hidden' => true,
            'remove_if_empty' => true,
        ],
        'date' => [
            'title' => 'Date',
            'format' => 'date',
            'class' => 'text-nowrap'
        ],
        'week_ending' => [
            'title' => 'Week Ending',
            'format' => 'date',
            'hidden' => true
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
            'default' => '&minus;'
        ],
        'comment' => [
            'title' => 'Comment',
            'default' => '&minus;',
            'hidden' => true,
            'remove_if_empty' => true,
        ],
        'rate' => [
            'title' => 'Rate',
            'format' => 'currency',
            'hidden' => true,
            'default' => '&minus;'
        ],
        'line_item_cost' => [
            'title' => 'Line Item Cost',
            'format' => 'currency'
        ]
    ];

    /**
     * @var array
     */
    private array $weekEndings = [];


    /**
     * @param Shift $shift
     * @return array
     * @throws \Exception
     */
    public function extractEntityData($shift): array
    {
        $date = $shift->date;
        if ( empty( $this->weekEndings[$date] ) ) {
            $this->weekEndings[$date] = (new DateTime( $date ))
                ->modify( 'next thursday' )
                ->format( 'd-m-Y' );
        }

        $minutes = $shift->getShiftLength();
        if ( $minutes === 0 && empty( $shift->timeFinished ) && !empty( $shift->timeStarted ) ) {
            $minutes = 'N/A';
        }
        return [
            'employee' => $this->htmlUtility::getButton( [
                    'element' => 'a',
                    'content' => $shift->employee->name,
                    'href' => $shift->employee->getLink(),
                    'class' => 'text-white'
                ] ) ?? $shift->employee->name,
            'job' => $this->htmlUtility::getButton( [
                'element' => 'a',
                'content' => $shift->job->id === 0 ? 'Factory' : $shift->job->id,
                'href' => $shift->job->getLink(),
                'class' => 'text-white'
            ] ),
            'furniture' => $this->htmlUtility::getButton( [
                'element' => 'a',
                'content' => $shift->furniture->name,
                'href' => $shift->furniture->getLink(),
                'class' => 'text-white'
            ] ),
            'date' => $date,
            'week_ending' => $this->weekEndings[$date],
            'time_started' => $shift->timeStarted,
            'time_finished' => $shift->timeFinished,
            'minutes' => $minutes,
            'hours' => $minutes,
            'activity' => $shift->activity->displayName,
            'comment' => $shift->activityComments,
            'rate' => $shift->employee->rate,
            'line_item_cost' => $shift->getShiftCost()
        ];
    }
}