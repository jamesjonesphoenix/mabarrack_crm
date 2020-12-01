<?php


namespace Phoenix\Report;


use Phoenix\Entity\JobOverPeriod;
use Phoenix\Entity\Jobs;
use Phoenix\Entity\JobsOverPeriod;

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
     * @var JobsOverPeriod
     */
    private JobsOverPeriod $jobs;

    /**
     * @var string
     */
    protected string $title = 'Profit/Loss';

    /**
     * @var int
     */
    private int $numberOfValidJobs;

    /**
     * @var string
     */
    protected string $emptyMessage = 'No jobs to report.';

    /**
     * @var bool
     */
    protected bool $includeColumnToggles = true;

    /**
     * @var bool
     */
    protected bool $includePrintButton = true;

    /**
     * @var string
     */
    protected string $tableClass = 'profit-loss';

    /**
     * @var int
     */
    protected int $countMinimum = 0;

    /**
     * @var string
     */
    // protected string $dateStart = '';

    /**
     * @var string
     */
    // protected string $dateFinish = '';

    /**
     * @var array
     */
    protected array $columns = [
        'item' => 'Item',
        'total' => [
            'title' => 'Total',
            'format' => 'currency'
        ],
        'average' => [
            'title' => 'Average',
            'format' => 'currency',
            'default' => 'N/A'
        ],
        'weighted_average' => [
            'title' => 'Weighted Average',
            'format' => 'currency',
            'default' => 'N/A'
        ],
        'percent_sum_cost' => [
            'title' => 'Average % of Sum Cost',
            'format' => 'percentage',
            'toggle_label' => 'Markup',
            'default' => 'N/A'
        ],
        'weighted_percent_sum_cost' => [
            'title' => 'Weighted Average % of Sum Cost',
            'format' => 'percentage',
            'toggle_label' => 'Weighted Markup'
        ],
        'margin' => [
            'title' => 'Average % of Sale Price',
            'format' => 'percentage',
            'toggle_label' => 'Gross Margin'
        ],
        'weighted_margin' => [
            'title' => 'Weighted Average % of Sale Price',
            'format' => 'percentage',
            'toggle_label' => 'Weighted Gross Margin'
        ],
    ];

    /**
     * @var array
     */
    protected array $rowArgs = [
        'employee_cost' => [
            'class' => 'bg-secondary'
        ],
        'sum_cost' => [
            'class' => 'bg-primary'
        ],
        'total_profit' => [
            'class' => 'bg-primary total-profit'
        ],
        'profit_header' => [
            'subheader' => true
        ],
        'factory' => [
            'class' => 'factory-costs'
        ],
        'sum_cost_without_factory' => [
            'class' => 'bg-secondary'
        ]
    ];

    /**
     * @var bool
     */
    private bool $includeFactoryCosts = false;

    /**
     * @var bool|
     */
    private bool $includeFactoryCostsButton = false;

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->numberOfValidJobs;
    }

    /**
     * @return $this
     */
    public function includeFactoryCosts(): self
    {
        $this->includeFactoryCosts = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function includeFactoryCostsButton(): self
    {
        $this->includeFactoryCostsButton = true;
        return $this;
    }

    /**
     * @param JobsOverPeriod $jobs
     * @return $this
     */
    public function setEntities(JobsOverPeriod $jobs): self
    {
        $this->jobs = $jobs;
        return $this;
    }

    /**
     * @return array
     */
    protected function extractData(): array
    {
        if ( $this->jobs->getCount() === 0 ) {
            return [];
        }
        /*
        $validJobs = $this->getValidJobs();
        if ( empty( $validJobs ) ) {
            return [];
        }
        */
        $this->numberOfValidJobs = 0;
        $data = $this->getEmptyRows();

        // $totalSales = $this->jobs->getTotalSales();

        // $jobsProportionsAndWeights = $this->jobs->getProportionsAndWeightsOverPeriod();

        $factoryJobCost = $this->includeFactoryCosts ?
            $this->jobs->getFactoryCost()
            : 0;


        foreach ( $this->jobs->getAll() as $jobID => $job ) {
            // $job->shifts->calculateCompletionOverPeriod()
            // $periodProportion = $job->shifts->calculateCompletionOverPeriod();
            // $jobProportionAndWeight = $jobsProportionsAndWeights[$job->id];

            if ( $job->id === 0 || !empty( $job->healthCheck() ) || !empty( $job->completeCheck() ) ) {
                continue;
            }
            $this->numberOfValidJobs++;
            $weight = $job->getWeight();


            $weightedFactoryJobCost = $weight * $factoryJobCost;
            $jobArray = $this->calculateJobArray( $job );
            $jobArray['sum_cost'] += $weightedFactoryJobCost;
            $jobArray['factory'] = $weightedFactoryJobCost;
            $jobArray['total_profit'] -= $weightedFactoryJobCost;
            $data = $this->addToRowsTotals( $data, $weight, $jobArray );

        }
        // d( $data );

        // $data['factory']['total'] = $factoryJobCost;
        // $data['sum_cost']['total'] += $factoryJobCost;

        if ( !$this->includeFactoryCosts ) {
            unset( $data['sum_cost_without_factory'], $data['factory'] );
        } else {
            $data['sum_cost']['item'] .= ' <small>Including Factory</small>';
        }
        if ( $this->numberOfValidJobs > 0 ) {
            $data = $this->calculateAverages( $data );
        }
        $data['profit_header'] = self::getProfitHeaderRow( $data['profit_header'] );
        return $data ?? [];
    }

    /**
     * @param array $data
     * @param float $weight
     * @param array $amounts
     * @return array
     */
    private function addToRowsTotals(array $data, float $weight, array $amounts): array
    {
        foreach ( $amounts as $rowID => $amount ) {
            $data[$rowID]['total'] += $amount;
            $data[$rowID]['weighted_average'] += $weight * $amount;
            if ( $amounts['sum_cost'] > 0 ) {
                $markup = $amount / $amounts['sum_cost'];
                $data[$rowID]['percent_sum_cost'] += $markup;
                $data[$rowID]['weighted_percent_sum_cost'] += $weight * $markup;
            }
            if ( $amounts['sale_price'] > 0 ) {
                $margin = $amount / $amounts['sale_price'];
                $data[$rowID]['margin'] += $margin;
                $data[$rowID]['weighted_margin'] += $weight * $margin;
            }
        }
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    private function calculateAverages(array $data = []): array
    {
        foreach ( $data as $rowID => &$row ) {
            $row['average'] = $row['total'] / $this->numberOfValidJobs;
            $row['percent_sum_cost'] /= $this->numberOfValidJobs;
            $row['margin'] /= $this->numberOfValidJobs;
        }
        return $data;
    }


    /**
     * @param array $row
     * @return array
     */
    private static function getProfitHeaderRow(array $row = []): array
    {
        return array_merge( $row, [
            'total' => 'Total',
            'average' => 'Average',
            'weighted_average' => 'Weighted Average',
            'percent_sum_cost' => 'Average Markup',
            'weighted_percent_sum_cost' => 'Weighted Average Markup',
            'margin' => 'Average Gross Margin',
            'weighted_margin' => 'Weighted Average Gross Margin',
        ] );
    }

    /**
     * @return array
     */
    private function getEmptyRows(): array
    {
        $emptyRows = [];
        foreach ( self::getRowTitles() as $rowID => $rowTitle ) {
            $emptyRows[$rowID] = [
                'item' => $rowTitle,
                'total' => 0,
                'weighted_average' => 0,
                'percent_sum_cost' => 0,
                'weighted_percent_sum_cost' => 0,
                'margin' => 0,
                'weighted_margin' => 0,
            ];
        }
        return $emptyRows ?? [];
    }

    /**
     * @return string[]
     */
    private static function getRowTitles(): array
    {
        return [
            'employee_cost_manual' => 'Employee Costs Manual',
            'employee_cost_cnc' => 'Employee Costs CNC',
            'employee_cost_general' => 'Employee Costs General*',
            'employee_cost' => 'Total Employee Costs',
            'material_cost' => 'Material Costs',
            'contractor_cost' => 'Contractor Costs',
            'spare_cost' => 'Spare Costs',
            'sum_cost_without_factory' => 'Sum Job Costs',
            'factory' => 'Factory Employee Costs',
            'sum_cost' => 'Sum Costs',
            'sale_price' => 'Sales Revenue',
            'profit_header' => 'Profit',
            'total_profit' => 'Total Profit',
        ];
    }

    /**
     * @return string
     */
    public function getTotalCountString(): string
    {
        return 'Jobs Included';
    }

    /**
     * @return array
     */
    public function buildNavLinks(): array
    {
        $url = $this->getURL();

        if ( $this->includeFactoryCostsButton ) {
            $return['include_factory_costs'] = [
                'href' => $url->setQueryArg( 'include_factory_costs', !$this->includeFactoryCosts )->write(),
                'content' => $this->includeFactoryCosts ? 'Ignore Factory Costs' : 'Include Factory Costs',
                'class' => 'bg-secondary',
            ];
        }
        return array_merge(
            $return ?? [],
            parent::buildNavLinks()
        );
    }

    /**
     * @param JobOverPeriod $job
     * @return array
     */
    private function calculateJobArray(JobOverPeriod $job): array
    {
        $array = [
            'employee_cost_cnc' => $job->shifts->getWorkerCost( 'CNC' ),
            'employee_cost_manual' => $job->shifts->getWorkerCost( 'Manual' ),
            'employee_cost_general' => $job->shifts->getWorkerCost( 'General' ),
            'employee_cost' => $job->shifts->getTotalWorkerCost(),
            'material_cost' => $job->materialCost,
            'contractor_cost' => $job->contractorCost,
            'spare_cost' => $job->spareCost,

            'sum_cost_without_factory' => $job->getTotalCost(),
            // 'factory' => $weightedFactoryJobCost,
            'sum_cost' => $job->getTotalCost(),

            'sale_price' => $job->salePrice,
            'total_profit' => $job->getTotalProfit(),
        ];
        $periodProportion = $job->getPeriodProportion();
        foreach ( $array as &$item ) {
            $item *= $periodProportion;
        }
        return $array;
    }

}