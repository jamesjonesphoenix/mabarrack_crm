<?php

namespace Phoenix\Report\Archive;

use Phoenix\Entity\JobOverPeriod;

/**
 * Class ArchiveTableJobs
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableProfitLossJobsValid extends ArchiveTable
{
    /**
     * @var string
     */
    protected string $title = 'Jobs Included in Report';

    /**
     * @var array
     */
    protected array $columns = [
        'date_started' => [
            'title' => 'Start Date',
            'format' => 'date',
            'default' => '&minus;',
            'class' => 'text-nowrap',
            'hidden' => true,
        ],
        'date_finished' => [
            'title' => 'Finish Date',
            'format' => 'date',
            'default' => 'Ongoing',
            'remove_if_empty' => true,
            'class' => 'text-nowrap',
            'hidden' => true,
        ],
        'priority' => [
            'title' => 'Priority',
            'hidden' => true,
        ],
        'status' => [
            'title' => 'Status',
            'hidden' => true,
            'default' => '&minus;'
        ],
        'customer' => [
            'title' => 'Customer',
            'hidden' => true,
        ],
        'furniture' => [
            'title' => 'Furniture',
            'hidden' => true,
        ],
        'description' => [
            'title' => 'Description',
            'default' => '&minus;',
            'hidden' => true
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
        ],
        'proportion' => [
            'title' => 'Proportion',
            'format' => 'percentage',
            'remove_if_empty' => true,
        ],
        'weight' => [
            'title' => 'Weight',
            'format' => 'percentageExtraDecimals',
            'remove_if_empty' => true,
        ],
        'errors' => [
            'title' => 'Reason for Exclusion',
            'hidden' => false
        ]
    ];

    /**
     * @var bool
     */
    protected bool $doNotMatchWidths = true;

    /**
     * @param JobOverPeriod $entity
     * @return string
     */
    public function getErrorString($entity): string
    {
        $healthCheck = !empty($entity->healthCheck()) ? '<h6 class="mt-2">Job has error:</h6>' . $this->htmlUtility::getListGroup( $entity->healthCheck() ) : '';

        $completeCheck = !empty($entity->completeCheck()) ? '<h6 class="mt-2">Job is incomplete:</h6>' . $this->htmlUtility::getListGroup( $entity->completeCheck() ) : '';

        return $healthCheck . $completeCheck;
    }

    /**
     * @param JobOverPeriod $job
     * @return array
     */
    public function extractMoreEntityData(JobOverPeriod $job): array
    {
        return [
            'proportion' => $job->getPeriodProportion(),
            'weight' => $job->getWeight()
        ];
    }

    /**
     * @param JobOverPeriod $job
     * @return array
     */
    public function extractEntityData($job): array
    {

        return array_merge( [
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
        ],
            $this->extractMoreEntityData( $job )
        );
    }
}