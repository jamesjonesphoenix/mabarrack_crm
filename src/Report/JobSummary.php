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
     *
     */
    protected string $title = 'Job Summary';

    /**
     * @var Job
     */
    protected Job $job;

    /**
     * @param Job $job
     * @return $this
     */
    public function init(Job $job): self
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @return array
     */
    public function extractData(): array
    {
        //$title = $this::title;

        $job = $this->job;
        $totalCost = $job->getTotalCost();
        $doCalculatePercentage = $totalCost !== (float)0;
        $totalProfit = $job->getTotalProfit();
        $naString = 'N/A';

        $currencyValues = [
            'employee_cost_manual' => $job->shifts->getWorkerCost( 'Manual' ),
            'employee_cost_cnc' => $job->shifts->getWorkerCost( 'CNC' ),
            'employee_cost_other' => $job->shifts->getWorkerCost( 'All' ),
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
        $currencyValues = $this->format::formatArrayValues( $currencyValues, 'currency' );
        if ( $doCalculatePercentage ) {
            $percentValues = $this->format::formatArrayValues( $percentValues, 'percentage' );
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
        $returnData['employee_cost_other']['notes'] = '<p class="mb-0"><small>*Activities recorded before we started recording CNC and Manual work separately.</small></p>';
        $returnData['profit_header'] = array_merge( $returnData['profit_header'], [
            'value' => 'Value',
            'percent_sum_cost' => '% of Sum Cost (Markup)',
            'notes' => '% of Sale Price (Gross Margin)',
        ] );
        $returnData['total_profit']['notes'] = (int)$job->salePrice !== 0 ? $this->format::percentage( $totalProfit / $job->salePrice ) : $naString; //gross margin

        return $returnData;
    }

    /**
     * @return string[]
     */
    private static function getRowTitles(): array
    {
        return [
            'employee_cost_manual' => 'Employee Cost Manual',
            'employee_cost_cnc' => 'Employee Cost CNC',
            'employee_cost_other' => 'Employee Cost Other*',
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
     * @return string
     * @throws \Exception
     */
    public function renderReport(): string
    {
        return $this->htmlUtility::getTableHTML( [
            'data' => $this->extractData(),
            'columns' => [
                'item' => 'Item',
                'value' => 'Value',
                'percent_sum_cost' => '% of Sum Cost',
                'notes' => ''
            ],
            'rows' => [
                'employee_cost' => ['class' => 'bg-primary'],
                'sum_cost' => ['class' => 'bg-primary'],
                'total_profit' => ['class' => 'bg-primary'],
                'profit_header' => ['subheader' => true]
            ]
        ] );

    }
}