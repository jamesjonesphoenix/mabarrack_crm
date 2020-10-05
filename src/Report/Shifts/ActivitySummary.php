<?php


namespace Phoenix\Report\Shifts;

/**
 * Class ActivitySummary
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class ActivitySummary extends ShiftsReport
{
    /**
     *
     */
    protected string $title = 'Activities Summary';

    /**
     * @var string
     */
    protected string $noShiftsMessage = 'No job activity to report.';

    /**
     * @return array
     */
    public function sortShifts(): array
    {
        foreach ( $this->shifts->getAll() as $shift ) {
            $type = $shift->activity->type ?? $shift->activity->category ?? '';
            $returnShifts[$type][$shift->id] = $shift;
        }
        return $returnShifts ?? [];
    }

    /**
     * @return array
     */
    public function getActivitiesSummary(): array
    {
        foreach ( $this->sortShifts() as $groupName => $shifts ) {

            foreach($shifts as $shift){
                if ( empty( $activitiesSummary[$groupName][$shift->activity->id] ) ) {
                    $activitiesSummary[$groupName][$shift->activity->id] = [
                        'activity_id' => $shift->activity->id,
                        'activity' => $shift->activity->displayName,
                        'activity_hours' => 0,
                        'activity_cost' => 0,
                    ];
                }
                $activitiesSummary[$groupName][$shift->activity->id]['activity_hours'] += $shift->getShiftLength();
                $activitiesSummary[$groupName][$shift->activity->id]['activity_cost'] += $shift->getShiftCost();
            }
        }
        if ( !empty( $activitiesSummary['Lunch'] ) ) {
            $lunchRow = $activitiesSummary['Lunch'];
            unset( $activitiesSummary['Lunch'] );
            $activitiesSummary = ['Lunch' => $lunchRow] + $activitiesSummary;
        }
        return $activitiesSummary ?? [];
    }

    /**
     * @return array
     */
    public function extractData(): array
    {
        $shifts = $this->shifts;
        if ( $shifts->getCount() === 0 ) {
            return [];
        }
        $activitiesSummary = $this->getActivitiesSummary();

        //krsort( $activitiesSummary );
        foreach ( $activitiesSummary as $groupName => $activities ) {
            ksort( $activities );
            $groupTotalMinutes = 0;
            $groupTotalCost = 0;
            foreach ( $activities as $activityID => $activity ) {
                $returnData[$activityID] = $activity;

                $groupTotalMinutes += $activity['activity_hours'];
                $groupTotalCost += $activity['activity_cost'];

            }
            $returnData['employee_time_' . strtolower( $groupName )] = [
                'activity_id' => 'Subtotal',
                'activity' => $groupName === 'All' ? 'Unspecific Time' : $groupName . ' Time',
                'activity_hours' => $groupTotalMinutes,
                'activity_cost' => $groupTotalCost,
            ];
        }

        //Activities recorded before we started recording CNC and Manual work separately.
        $returnData['total_time'] = [
            'activity' => 'Total Hours',
            'activity_hours' => $shifts->getTotalWorkerMinutes(),
            'activity_cost' => $shifts->getTotalWorkerCost(),
        ];

        foreach ( $returnData as &$activity ) {
            $activity['%_of_total_hours'] = $shifts->getTotalWorkerMinutes() > 0 ? $activity['activity_hours'] / $shifts->getTotalWorkerMinutes() : 0;
            $activity['%_of_total_employee_cost'] = $shifts->getTotalWorkerCost() > 0 ? $activity['activity_cost'] / $shifts->getTotalWorkerCost() : 0;
        }
        return $returnData;
    }

    public function getNavLinks(): array
    {
        return [
            [
                'url' => '#',
                'text' => 'Billable vs Non-Billable'
            ]
        ];
    }


    /**
     * @return string[]
     */
    private function getColumns(): array
    {
        return [
            'activity_id' => 'Activity ID',
            'activity' => 'Activity',
            'activity_hours' => 'Activity Hours',
            '%_of_total_hours' => '% of Total Hours',
            'activity_cost' => 'Activity Cost',
            '%_of_total_employee_cost' => '% of Total Employee Cost'
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function renderReport(): string
    {
        $data = $this->extractData();
        if ( empty( $data ) ) {
            return $this->htmlUtility::getAlertHTML( $this->noShiftsMessage, 'warning', false );
        }
        $data = $this->format::formatColumnValues( $data, 'percentage', '%_of_total_hours' );
        $data = $this->format::formatColumnValues( $data, 'percentage', '%_of_total_employee_cost' );

        $data = $this->format::formatColumnValues( $data, 'hoursminutes', 'activity_hours' );
        $data = $this->format::formatColumnValues( $data, 'currency', 'activity_cost' );

        return $this->htmlUtility::getTableHTML( [
            'data' => $data,
            'columns' => $this->getColumns(),
            'rows' => [
                'employee_time_general' => ['class' => 'bg-secondary'],
                'employee_time_manual' => ['class' => 'bg-secondary'],
                'employee_time_cnc' => ['class' => 'bg-secondary'],
                'employee_time_lunch' => ['class' => 'bg-secondary'],
                'billable_time' => ['class' => 'bg-info'],
                'non_billable_time' => ['class' => 'bg-info'],

                'total_time' => ['class' => 'bg-primary']
            ],
        ] );
    }
}