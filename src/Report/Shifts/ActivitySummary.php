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
    public function extractData(): array
    {
        $shifts = $this->shifts;
        $activitiesSummary = [];

        if ( $shifts->getCount() === 0 ) {
            return [];
        }
        foreach ( $shifts->getAll() as $shift ) {
            $type = $shift->activity->type ?? $shift->activity->category ?? '';
            if ( $shift->activity->id === null ) {
                $missingActivities[$shift->id] = $shift;
                continue;
            }
            if ( empty( $activitiesSummary[$type][$shift->activity->id] ) ) {
                //$activity_id = $activities->getID( $shift[ 'activity' ] );
                $activitiesSummary[$type][$shift->activity->id] = [
                    'activity_id' => $shift->activity->id,
                    'activity' => $shift->activity->displayName,
                    'activity_hours' => 0,
                    'activity_cost' => 0,
                ];
            }
            $activitiesSummary[$type][$shift->activity->id]['activity_hours'] += $shift->getShiftLength();
            $activitiesSummary[$type][$shift->activity->id]['activity_cost'] += $shift->getShiftCost();
        }
        //krsort( $activitiesSummary );
        if(!empty($activitiesSummary['Lunch'])) {
            $v = $activitiesSummary['Lunch'];
            unset( $activitiesSummary['Lunch'] );
            $activitiesSummary['Lunch'] = $v;
        }
        foreach ( $activitiesSummary as $activityType => $summary ) {
            ksort( $summary );
            foreach ( $summary as $activityID => $activity ) {
                $returnData[$activityID] = $activity;
            }
            $returnData['employee_time_' . strtolower( $activityType )] = [
                'activity_id' => 'Subtotal',
                'activity' => $activityType === 'All' ? 'Unspecific Time' : $activityType . ' Time',
                'activity_hours' => $shifts->getWorkerMinutes( $activityType ),
                'activity_cost' => $shifts->getWorkerCost( $activityType ),
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
                'employee_time_all' => ['class' => 'bg-secondary'],
                'employee_time_lunch' => ['class' => 'bg-secondary'],
                'total_time' => ['class' => 'bg-primary']
            ],
        ] );
    }
}