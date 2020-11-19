<?php

namespace Phoenix\Report;

use Phoenix\Entity\Job;

/**
 * Class JobSummary
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class JobSummary extends Report
{
    /**
     * @var string
     */
    protected string $title = 'Job Summary';

    /**
     * @var Job
     */
    protected Job $job;

    /**
     * @var array
     */
    protected array $columns = [
        'item' => 'Item',
        'value' => ['title' => 'Value', 'format' => 'currency'],
        'percent_sum_cost' => ['title' => '% of Sum Cost', 'format' => 'percentage'],
        'notes' => ['title' => '', 'format' => 'percentage']
    ];

    /**
     * @var array
     */
    protected array $rowArgs = [
        'employee_cost' => ['class' => 'bg-primary'],
        'sum_cost' => ['class' => 'bg-primary'],
        'total_profit' => ['class' => 'bg-primary'],
        'profit_header' => ['subheader' => true]
    ];

    /**
     * @return string[]
     */
    private static function getRowTitles(): array
    {
        return [
            'employee_cost_manual' => 'Employee Cost Manual',
            'employee_cost_cnc' => 'Employee Cost CNC',
            'employee_cost_general' => 'Employee Cost General*',
            'employee_cost' => 'Total Employee Cost',
            'material_cost' => 'Material Cost',
            'contractor_cost' => 'Contractor Cost',
            'spare_cost' => 'Spare Cost',
            'sum_cost' => 'Sum Costs',
            'sale_price' => 'Sale Price',
            'profit_header' => 'Profit',
            'total_profit' => 'Total Profit',
        ];
    }

    /**
     * @var bool
     */
    protected bool $printButton = true;

    /**
     * @param Job $job
     * @return $this
     */
    public function setJob(Job $job): self
    {
        if ( $job->id !== null ) {
            $this->setTitle( $this->getTitle() . ' ' . $job->getIDBadge() );
        }
        $this->job = $job;
        return $this;
    }

    /**
     * @return array
     */
    protected function extractData(): array
    {
        //$title = $this::title;

        $job = $this->job;
        $totalCost = $job->getTotalCost();
        $doCalculatePercentage = $totalCost !== (float)0;
        $totalProfit = $job->getTotalProfit();
        $naString = 'N/A';

        // d($job->shifts);

        $currencyValues = [
            'employee_cost_manual' => $job->shifts->getWorkerCost( 'Manual' ),
            'employee_cost_cnc' => $job->shifts->getWorkerCost( 'CNC' ),
            'employee_cost_general' => $job->shifts->getWorkerCost( 'General' ),
            'employee_cost' => $job->shifts->getTotalWorkerCost(),
            'material_cost' => $job->materialCost,
            'contractor_cost' => $job->contractorCost,
            'spare_cost' => $job->spareCost,
            'sum_cost' => $totalCost,
            'sale_price' => $job->salePrice,
            'total_profit' => $totalProfit,
        ];
        foreach ( $currencyValues as $rowName => $currencyValue ) {
            $percentValues[$rowName] = $doCalculatePercentage ? $currencyValue / $totalCost : $naString;
        }

        foreach ( self::getRowTitles() as $rowName => $rowTitle ) {
            $returnData[$rowName] = [
                'item' => $rowTitle,
                'value' => $currencyValues[$rowName] ?? '',
                'percent_sum_cost' => $percentValues[$rowName] ?? '',
                'notes' => ''
            ];
        }
        //Add "custom" table items
        foreach ( $job->shifts->getAll() as $shift ) {
            if ( !$shift->activity->isActive() ) {
                $returnData['employee_cost_general']['notes'] = '<p class="mb-0 d-print-none"><small>*Includes activities recorded before CNC and Manual specific activities were created.</small></p>';
                break;
            }
        }
        $returnData['profit_header'] = array_merge( $returnData['profit_header'] ?? [], [
            'value' => 'Value',
            'percent_sum_cost' => '% of Sum Cost (Markup)',
            'notes' => '% of Sale Price (Gross Margin)',
        ] );
        $returnData['total_profit']['notes'] = (int)$job->salePrice !== 0 ? $totalProfit / $job->salePrice : $naString; //gross margin

        return $returnData;
    }
}