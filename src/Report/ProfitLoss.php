<?php


namespace Phoenix\Report;


use Phoenix\Entity\Job;

/**
 * Class ProfitLoss
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class ProfitLoss extends Report
{
    /**
     * @var Job[]
     */
    private array $jobs = [];

    private string $dateStart;

    private string $dateFinish;

    private int $numberOfValidJobs;

    /**
     * @param Job[]  $jobs
     * @param string $dateStart
     * @param string $dateFinish
     * @return $this
     */
    public function init(array $jobs = [], string $dateStart = '', string $dateFinish = ''): self
    {
        $this->jobs = $jobs;
        $this->dateStart = $dateStart;
        $this->dateFinish = $dateFinish;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Profit/Loss' ;
    }

    /**
     * @return array
     */
    public function extractData(): array
    {
        $data = [];
        $totalValues = [];
        $weightedAverageValues = [];
        foreach ( self::getRowTitles() as $rowName => $rowTitle ) {
            $totalValues[$rowName] = 0;
            $weightedAverageValues[$rowName] = 0;
        }
        $numberOfJobs = 0;
        $validJobs = [];
        $invalidJobs = [];
        foreach ( $this->jobs as $job ) {
            if ( empty( $job->dateFinished ) || $job->id === 0 || empty( $job->salePrice ) ) {
                $invalidJobs[$job->id] = $job;
                continue;
            }
            $validJobs[$job->id] = $job;
        }
        $this->numberOfValidJobs = count( $validJobs );
        d($this->numberOfValidJobs);
        foreach ( $validJobs as $job ) {
            // $reportPeriodShifts = $jobShifts->getShiftsOverTimespan( $this->dateStart, $this->dateFinish );
            $totalValues['sale_price'] += $job->salePrice;
        }
        $weightedGrossMargin = 0;
        foreach ( $validJobs as $job ) {
            $totalProfit = $job->getTotalProfit();
            $totalValues['total_profit'] += $totalProfit;
            $weight = $job->salePrice / $totalValues['sale_price'];
            $weightedGrossMargin += $weight * $totalProfit / $job->salePrice; //gross margin


            //$markup = $totalProfit / $job->getTotalCost();
//d($job->shifts);
            $totalValues['employee_cost_manual'] += $job->shifts->getWorkerCost( 'Manual' );
            $weightedAverageValues['employee_cost_manual'] += $weight * $job->shifts->getWorkerCost( 'Manual' );
            $totalValues['employee_cost_cnc'] += $job->shifts->getWorkerCost( 'CNC' );
            $weightedAverageValues['employee_cost_cnc'] += $weight * $job->shifts->getWorkerCost( 'CNC' );
            $totalValues['employee_cost_other'] += $job->shifts->getWorkerCost( 'All' );
            $totalValues['employee_cost'] += $job->shifts->getTotalWorkerCost();
            $totalValues['material_cost'] += $job->materialCost;
            $totalValues['contractor_cost'] += $job->contractorCost;
            $totalValues['spare_cost'] += $job->spareCost;
            $totalValues['sum_cost'] += $job->getTotalCost();
        }
        $averageValues = [];
        foreach ( $totalValues as $rowName => $totalValue ) {
            $averageValues[$rowName] = $totalValue / $this->numberOfValidJobs;

        }

        $totalValues = $this->format::formatArrayValues( $totalValues, 'currency' );
        $averageValues = $this->format::formatArrayValues( $averageValues, 'currency' );
        foreach ( self::getRowTitles() as $rowName => $rowTitle ) {
            $data[$rowName] = [
                'item' => $rowTitle,
                'total' => $totalValues[$rowName] ?? '',
                'average' => $averageValues[$rowName],
                'weighted' => $weightedAverageValues[$rowName],
                //'percent_sum_cost' => $percentValues[$rowName] ?? '',
                //'notes' => ''
            ];
        }
        $data['total_profit']['weighted'] = $this->format::percentage($weightedGrossMargin);
        $data['profit_header'] = array_merge( $data['profit_header'], [
            'total' => 'Total',
            'average' => 'Average',
            'weighted' => 'Weighted Average Gross Margin',
        ] );

        return $data ?? [];
    }

    /**
     * @return string[]
     */
    private static function getRowTitles(): array
    {
        return [
            'employee_cost_manual' => 'Employee Costs Manual',
            'employee_cost_cnc' => 'Employee Costs CNC',
            'employee_cost_other' => 'Employee Costs Other*',
            'employee_cost' => 'Total Employee Costs',
            'material_cost' => 'Material Costs',
            'contractor_cost' => 'Contractor Costs',
            'spare_cost' => 'Spare Costs',
            'sum_cost' => 'Sum Costs',
            'sale_price' => 'Sales Revenue',
            'profit_header' => 'Profit',
            'total_profit' => 'Total Profit',
            'p_header' => 'Profit',
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function renderReport(): string
    {
        $data = $this->extractData();
        return $this->htmlUtility::getTableHTML( [
            'data' => $data,
            'columns' => [
                'item' => 'Item',
                'total' => 'Total',
                'average' => 'Average Per Job',
                'weighted' => 'Weighted Average Per Job',
            ],
            'rowsClasses' => [
                'employee_cost' => 'bg-primary',
                'sum_cost' => 'bg-primary',
                'total_profit' => 'bg-primary',
            ],
            'subheaders' => [
                'rows' => ['profit_header']
            ]
        ] );
    }
}