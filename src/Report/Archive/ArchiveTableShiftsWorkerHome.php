<?php

namespace Phoenix\Report\Archive;

use Phoenix\Entity\Entity;
use Phoenix\Entity\Shift;

/**
 * Class ArchiveTableShiftsWorkerHome
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableShiftsWorkerHome extends ArchiveTable
{
    /**
     * @var array
     */
    protected array $columns = [
        'id' => [
            'hidden' => true
        ],
        'job' => [
            'title' => 'Job',
            'hidden' => true
        ],
        'customer' => [
            'title' => 'Customer',
            'default' =>'&minus;'
        ],
        'description' => [
            'title' => 'Description',
            'default' => '&minus;',
            'remove_if_empty' => true,
        ],
        'date' => [
            'title' => 'Date',
            'format' => 'annotateDateAllDays'
        ],
        'time_started' => [
            'title' => 'Time Started',
            'default' => '&minus;'
        ],
        'time_finished' => [
            'title' => 'Time Finished',
            'default' => '&minus;'
        ],
        'furniture' => [
            'title' => 'Furniture',
            'hidden' => true,
            'remove_if_empty' => true,
            'default' => '&minus;'
        ],
        'minutes' => [
            'title' => 'Minutes',
            'format' => 'number',
            'hidden' => true
        ],
        'hours' => [
            'title' => 'Hours',
            'format' => 'hoursminutes',
            'hidden' => true
        ],
        'activity' => [
            'title' => 'Activity',
            'default' => 'Unknown Activity'
        ],
        'comment' => [
            'title' => 'Comment',
            'remove_if_empty' => true,
            'default' => '&minus;'
        ]
    ];

    /**
     * @var bool
     */
    protected bool $includePrintButton = false;

    /**
     * @var bool
     */
    protected bool $includeAddNewButton = false;

    /**
     * @param Shift $entity
     * @return string
     */
    public function getActionButton($entity): string
    {
        return $this->htmlUtility::getViewButton(
            'worker.php?other_comment=1&shift=' . $entity->id,
            !empty($entity->activityComments) ? 'Edit Comment' : 'Add Comment'
        );
    }

    /**
     * @param Shift $shift
     * @return array
     */
    public function extractEntityData($shift): array
    {
        if ( $shift->activity->id === 0 ) {
            $description = 'Lunch';
        } else {
            $description = $shift->job->id === 0 ? 'Non-billable internal factory work.' : $shift->job->description;
        }

        $minutes = $shift->getShiftLength();
        if ( $minutes === 0 && empty( $shift->timeFinished ) && !empty( $shift->timeStarted ) ) {
            $minutes = 'N/A';
        }

        return [
            'job' => $shift->job->id === 0 ? 'Factory' : $shift->job->id,
            'customer' => $shift->job->customer->name,
            'description' => $description,
            'date' => $shift->date,
            'time_started' => $shift->timeStarted,
            'time_finished' => $shift->timeFinished,
            'furniture' => $shift->furniture->name,
            'minutes' => $minutes,
            'hours' => $minutes,
            'activity' => $shift->activity->displayName,
            'comment' => $shift->activityComments
        ];
    }
}