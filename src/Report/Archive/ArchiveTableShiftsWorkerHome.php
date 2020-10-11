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
            'default' => '&minus;'
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
            'hidden' => true
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
    protected bool $printButton = false;

    /**
     * @return string
     */
    public function getAdditionalHeaderHTML(): string
    {
        return '';
    }

    /**
     * @param Entity $entity
     * @return string
     */
    public function getActionButton(Entity $entity): string
    {
        return $this->htmlUtility::getViewButton(
            'worker.php?other_comment=1&shift=' . $entity->id,
            'Add Comment'
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
            'furniture' => $shift->getFurnitureString(),
            'minutes' => $minutes,
            'hours' => $minutes,
            'activity' => $shift->activity->displayName,
            'comment' => $shift->activityComments
        ];
    }
}