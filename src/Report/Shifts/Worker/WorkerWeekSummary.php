<?php


namespace Phoenix\Report\Shifts\Worker;


/**
 * Class WeeklySummary
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class WorkerWeekSummary extends WorkerWeekReport
{

    /**
     * @var string
     */
    protected string $title = 'Worker Week Summary';

    /**
     * @var array
     */
    protected array $columns = [
        'item' => 'Item',
        'hours' => [
            'title' => 'Hours',
            'format' => 'hoursminutes'
        ],
        'percent_hours_paid' => [
            'title' => 'Percent of Hours Paid',
            'format' => 'percentage'
        ],
        'percent_hours_total' => [
            'title' => 'Percent of Total Recorded',
            'format' => 'percentage'
        ]
    ];

    /**
     * @var array
     */
    protected array $rowArgs = [
        'total_recorded' => ['class' => 'bg-primary'],
        'total_paid' => ['class' => 'bg-primary'],
        'factory_all' => ['class' => 'bg-primary'],
    ];

    /**
     * @return array
     */
    protected function extractData(): array
    {
        if ( $this->shifts->getCount() === 0 ) {
            return [];
        }
        $totals = [
            'total_value_adding' => 0,
            'total_non_chargeable' => 0,
            'total_recorded' => 0,

            'factory_all' => 0, //total time spent on factory
            'factory_with_job_number' => 0,
            'factory_without_job_number' => 0,

            'lunch' => 0,
            'total_paid' => 0 //non lunch minutes
        ];
        foreach ( $this->shifts->getAll() as $shift ) {
            $shiftLength = $shift->getShiftLength();
            if ( $shift->activity->chargeable ) {
                $totals['total_value_adding'] += $shiftLength;
            } else {
                $totals['total_non_chargeable'] += $shiftLength;
            }
            $totals['total_recorded'] += $shiftLength;

            if ( $shift->isLunch() === 'lunch' ) {
                $totals['lunch'] += $shiftLength;
            } else {
                if ( $shift->job->customer->id === 0 ) { //internal job
                    $totals['factory_all'] += $shiftLength;
                    if ( $shift->job->id === 0 ) {
                        $totals['factory_without_job_number'] += $shiftLength; //add minutes of this shift to total
                    } elseif ( $shift->job->id > 0 ) {
                        $totals['factory_with_job_number'] += $shiftLength;
                    }
                }

                $totals['total_paid'] += $shiftLength;
            }
        }

        foreach ( $this->getRowTitles() as $key => $item ) {
            $returnData[$key]['item'] = $item;
            $returnData[$key]['hours'] = $totals[$key];
            if ( $totals['total_paid'] !== 0 ) {
                $returnData[$key]['percent_hours_paid'] = $totals[$key] / $totals['total_paid'];
            } else {
                $returnData[$key]['percent_hours_paid'] = 0;
            }
            if ( $totals['total_recorded'] !== 0 ) {
                $returnData[$key]['percent_hours_total'] = $totals[$key] / $totals['total_recorded'];
            } else {
                $returnData[$key]['percent_hours_total'] = 0;
            }
        }
        $returnData['lunch']['percent_hours_paid'] = 0;
        return $returnData ?? [];
    }

    /**
     * @return array
     */
    private function getRowTitles(): array
    {
        return [
            'total_value_adding' => 'Total Value Adding',
            'total_non_chargeable' => 'Total Non Chargeable',
            'total_recorded' => 'Total Recorded',

            'factory_with_job_number' => 'Factory - With Job Number',
            'factory_without_job_number' => 'Factory - Without Job Number',
            'factory_all' => 'Factory - Any Type',
            'lunch' => 'Lunch',
            'total_paid' => 'Total To Be Paid'
        ];
    }
}