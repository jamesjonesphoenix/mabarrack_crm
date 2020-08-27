<?php


namespace Phoenix\Report\Archive;


use Phoenix\Entity\Job;

/**
 * Class ArchiveTableCustomerJobs
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableCustomerJobs extends ArchiveTable
{
    /**
     * @var array
     */
    protected array $columns = [
        'date_started' => [
            'title' => 'Start Date',
            'format' => 'date'
        ],
        'date_finished' => [
            'title' => 'Finish Date',
            'format' => 'date'
        ],
        'priority' => [
            'title' => 'Priority'
        ],
        'furniture' => [
            'title' => 'Furniture'
        ],
        'description' => [
            'title' => 'Description'
        ]
    ];

    /**
     * @param Job $job
     * @return array
     */
    public function extractEntityData($job): array
    {
        return [
            'id' => $job->id,
            'date_started' => $job->dateStarted,
            'date_finished' => $job->dateFinished ?? 'Ongoing',
            'status' => $job->status,
            'priority' => $job->priority,
            'furniture' => $job->getFurnitureString(),
            'description' => $job->description,
            'view' => $this->htmlUtility::getViewButton($job->getLink(), 'View Job')
        ];
    }
}