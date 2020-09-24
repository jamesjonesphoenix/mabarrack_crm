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
            'format' => 'date',
            'default' => 'Ongoing'
        ],
        'priority' => [
            'title' => 'Priority'
        ],
        'furniture' => [
            'title' => 'Furniture'
        ],
        'description' => [
            'title' => 'Description'
        ],
        'markup' => [
            'title' => 'Markup',
            'format' => 'percentage',
            'hidden' => true,
            'default' => 'N/A'
        ],
        'profit_loss' => [
            'title' => 'Profit/Loss',
            'format' => 'currency',
            'hidden' => true
        ],
        'employee_cost' => [
            'title' => 'Employee Cost',
            'format' => 'currency',
            'hidden' => true
        ],
        'number_of_shifts' => [
            'title' => 'Number of Shifts',
            'format' => 'number',
            'hidden' => true
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
            'date_finished' => $job->dateFinished,
            'status' => $job->status,
            'priority' => $job->priority,
            'furniture' => $job->getFurnitureString(),
            'description' => $job->description,
            'markup' => $job->getMarkup(),
            'profit_loss' => $job->getTotalProfit(),
            'employee_cost' => $job->shifts->getTotalWorkerCost(),
            'number_of_shifts' => $job->shifts->getCount(),
            //'view' => $this->htmlUtility::getViewButton($job->getLink(), 'View Job')
        ];
    }
}