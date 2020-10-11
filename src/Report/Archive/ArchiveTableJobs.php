<?php


namespace Phoenix\Report\Archive;


use Phoenix\Entity\Job;

/**
 * Class ArchiveTableJobs
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableJobs extends ArchiveTable
{
    /**
     * @var array
     */
    protected array $columns = [
        'date_started' => [
            'title' => 'Start Date',
            'format' => 'date',
            'default' => '&minus;',
            'class' => 'text-nowrap'
        ],
        'date_finished' => [
            'title' => 'Finish Date',
            'format' => 'date',
            'default' => 'Ongoing',
            'remove_if_empty' => true,
            'class' => 'text-nowrap'
        ],
        'priority' => [
            'title' => 'Priority'
        ],
        'status' => [
            'title' => 'Status',
            'hidden' => true,
            'default' => '&minus;'
        ],
        'customer' => [
            'title' => 'Customer'
        ],
        'furniture' => [
            'title' => 'Furniture'
        ],
        'description' => [
            'title' => 'Description',
            'inessential' => true,
            'default' => '&minus;'
        ],
        'markup' => [
            'title' => 'Markup',
            'format' => 'percentage',
            'inessential' => true,
            'default' => 'N/A'
        ],
        'profit_loss' => [
            'title' => 'Profit/Loss',
            'format' => 'currency',
            'inessential' => true
        ],
        'employee_cost' => [
            'title' => 'Employee Cost',
            'format' => 'currency',
            'inessential' => true
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
            'date_started' => $job->dateStarted,
            'date_finished' => $job->dateFinished,
            'priority' => $job->priority,
            'status' => !empty( $job->status->value ) ? '<span class="text-nowrap">' . $job->status->value . '</span>' : '',
            'customer' => $this->htmlUtility::getButton( [
                    'element' => 'a',
                    'content' => $job->customer->name,
                    'href' => $job->customer->getLink(),
                    'class' => 'text-white'
                ] ) ?? $job->customer->name,
            'furniture' => $job->getFurnitureString(),
            'description' => $job->description,
            'markup' => $job->getMarkup(),
            'profit_loss' => $job->getTotalProfit(),
            'employee_cost' => $job->shifts->getTotalWorkerCost(),
            'number_of_shifts' => $job->shifts->getCount(),
        ];
    }
}