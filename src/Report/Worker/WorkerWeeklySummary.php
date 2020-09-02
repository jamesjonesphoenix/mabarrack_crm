<?php


namespace Phoenix\Report\Worker;


/**
 * Class WeeklySummary
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class WorkerWeeklySummary extends WorkerReport
{

    /**
     * @var string
     */
    protected string $title = 'Worker Week Summary';

    /**
     * @return array
     */
    public function extractData(): array
    {

        $totals['total_value_adding'] = 0;
        $totals['total_non_chargeable'] = 0;
        $totals['total_recorded'] = 0;

        $totals['factory_all'] = 0; //total time spent on factory
        $totals['factory_with_job_number'] = 0;
        $totals['factory_without_job_number'] = 0;

        $totals['lunch'] = 0;
        $totals['total_paid'] = 0; //non lunch minutes

        foreach ( $this->shifts->getAll() as $shift ) {
            $shiftLength = $shift->getShiftLength();
            $totals['total_value_adding'] += $shift->activity->chargeable ? $shiftLength : 0;
            $totals['total_non_chargeable'] += !$shift->activity->chargeable ? $shiftLength : 0;
            $totals['total_recorded'] += $shiftLength;

            if ( $shift->job->customer === 0 && $shift->activity->name !== 'Lunch' ) { //internal job
                $totals['factory_all'] += $shiftLength;
                if ( $shift->job->id === 0 ) {
                    $totals['factory_without_job_number'] += $shiftLength; //add minutes of this shift to total
                } elseif ( $shift->job->id > 0 ) {
                    $totals['factory_with_job_number'] += $shiftLength;
                }


            }
            if ( $shift->activity->displayName === 'Lunch' ) {
                $totals['lunch'] += $shiftLength;
            } else {
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

    /**
     * @return string
     * @throws \Exception
     */
    public function renderReport(): string
    {
        $html = '';
        if ( $this->shifts->getCount() === 0 ) {
            $html .= $this->htmlUtility::getAlertHTML( 'No completed shifts found from <strong>' . $this->getDateStart() . '</strong> to <strong>' . $this->getDateFinish() . '</strong> to summarise.', 'warning', false );
        }
        $data = $this->extractData();
        if ( empty( $data ) ) {
            return $this->htmlUtility::getAlertHTML( 'Shifts found from <strong>' . $this->getDateStart() . '</strong> to <strong>' . $this->getDateFinish() . '</strong> but no summary data generated. Something has gone wrong.', 'danger' );
        }
        $data = $this->format::formatColumnValues( $data, 'hoursminutes', 'hours' );
        $data = $this->format::formatColumnValues( $data, 'percentage', 'percent_hours_paid' );
        $data = $this->format::formatColumnValues( $data, 'percentage', 'percent_hours_total' );

        return $html . $this->htmlUtility::getTableHTML( [
            'data' => $data,
            'columns' => [
                'item' => 'Item',
                'hours' => 'Hours',
                'percent_hours_paid' => 'Percent of Hours Paid',
                'percent_hours_total' => 'Percent of Total Recorded'
            ],
            'rowsClasses' => [
                'total_recorded' => 'bg-primary',
                'total_paid' => 'bg-primary',
                'factory_all' => 'bg-secondary',
            ]
        ] );
    }
}