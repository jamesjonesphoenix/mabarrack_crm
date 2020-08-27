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
            'format' => 'date'
        ],
        'date_finished' => [
            'title' => 'Finish Date',
            'format' => 'date'
        ],
        'priority' => [
            'title' => 'Priority'
        ],
        'customer' => [
            'title' => 'Customer'
        ],
        'furniture' => [
            'title' => 'Furniture'
        ],
        'description' => [
            'title' => 'Description'
        ],
        'markup' => [
            'title' => 'Markup',
            'format' => 'percentage'
        ],
        'profit_loss' => [
            'title' => 'Profit/Loss',
            'format' => 'currency',
        ],
        'employee_cost' => [
            'title' => 'Employee Cost',
            'format' => 'currency',
        ],
        'number_of_shifts' => [
            'title' => 'Number of Shifts',
            'format' => 'number',
            'hidden' => true
        ]
    ];

    /**
     * @param false $errorEntitiesOnly
     * @return $this
     */
    public function hideInessentialColumns($errorEntitiesOnly = false): self
    {
        if ( $errorEntitiesOnly ) {
            $this->columns['description']['hidden'] = true;
            $this->columns['markup']['hidden'] = true;
            $this->columns['profit_loss']['hidden'] = true;
            $this->columns['employee_cost']['hidden'] = true;
        }
        return parent::hideInessentialColumns();
    }

    /**
     * @param Job $job
     * @return array
     */
    public function extractEntityData($job): array
    {
        return [
            'date_started' => $job->dateStarted ?? '&minus;',
            'date_finished' => $job->dateFinished ?? 'Ongoing',
            'priority' => $job->priority,
            'customer' => $this->htmlUtility::getButton( [
                    'element' => 'a',
                    'content' => $job->customer->name,
                    'href' => $job->customer->getLink(),
                    'class' => 'text-white'
                ] ) ?? $job->customer->name,
            'furniture' => $job->getFurnitureString(),
            'description' => $job->description ?? '&minus;',
            'markup' => $job->getMarkup(),
            'profit_loss' => $job->getTotalProfit(),
            'employee_cost' => $job->shifts->getTotalWorkerCost(),
            'number_of_shifts' => $job->shifts->getCount(),
        ];
    }
}