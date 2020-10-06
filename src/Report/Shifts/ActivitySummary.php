<?php


namespace Phoenix\Report\Shifts;

use Phoenix\Entity\Shifts;
use Phoenix\Report\PeriodicReport;

/**
 * Class ActivitySummary
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class ActivitySummary extends PeriodicReport
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
     * @var array
     */
    protected array $rowArgs = ['total_time' => ['class' => 'bg-primary']];

    /**
     * @var array
     */
    protected array $columns = [
        'activity_id' => 'Activity ID',
        'activity' => 'Activity',
        'activity_hours' => ['title' => 'Activity Hours', 'format' => 'hoursminutes'],
        '%_of_total_hours' => ['title' => '% of Total Hours', 'format' => 'percentage'],
        'activity_cost' => ['title' => 'Activity Cost', 'format' => 'currency'],
        '%_of_total_employee_cost' => ['title' => '% of Total Employee Cost', 'format' => 'percentage']
    ];

    /**
     * @var Shifts
     */
    protected Shifts $shifts;

    /**
     * @param string $noShiftsMessage
     * @return $this
     */
    public function setNoShiftsMessage( string $noShiftsMessage = ''): self
    {
        $this->noShiftsMessage = $noShiftsMessage;
        return $this;
    }

    /**
     * @param Shifts $shifts
     * @return $this
     */
    public function setShifts(Shifts $shifts): self
    {
        $this->shifts = $shifts;
        return $this;
    }
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

            foreach ( $shifts as $shift ) {
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
        $fullRowName = 'row';
//        $fullRowHandle = $fullRowName . '.';
        foreach ( $this->getActivitiesSummary() as $groupName => $activities ) {

            $subtotalRow = 'employee_time_' . strtolower( $groupName );
/*
            $subheaderRow = $subtotalRow . 'subheader';
            $this->rowArgs[$subheaderRow] = ['subheader' => true, 'class' => 'bg-secondary'];
            $returnData[$subheaderRow] = [
                $fullRowName => ucwords( $groupName . ' Activities' ),
            ];

*/
            ksort( $activities );
            $groupTotalMinutes = 0;
            $groupTotalCost = 0;
            foreach ( $activities as $activityID => $activity ) {
                $returnData[$activityID] = $activity;

                $groupTotalMinutes += $activity['activity_hours'];
                $groupTotalCost += $activity['activity_cost'];

            }

            $returnData[$subtotalRow] = [
                 'activity_id' => 'Subtotal',
                 'activity' => $groupName === 'All' ? 'Unspecific Time' : $groupName . ' Time',
                 'activity_hours' => $groupTotalMinutes,
                 'activity_cost' => $groupTotalCost,
            ];
            $this->rowArgs[$subtotalRow] = ['class' => 'bg-secondary'];
        }

        $returnData['total_time'] = [
             'activity' => 'Total Hours',
             'activity_hours' => $shifts->getTotalWorkerMinutes(),
             'activity_cost' => $shifts->getTotalWorkerCost(),
        ];

        foreach ( $returnData as &$activity ) {
            $activity[ '%_of_total_hours'] = $shifts->getTotalWorkerMinutes() > 0 ? $activity['activity_hours'] / $shifts->getTotalWorkerMinutes() : 0;
            $activity[ '%_of_total_employee_cost'] = $shifts->getTotalWorkerCost() > 0 ? $activity['activity_cost'] / $shifts->getTotalWorkerCost() : 0;
        }
        return $returnData;
    }



    /*
      private function gzdfgdfgs(): array
      {
          $fullRowHandle = 'row.';
          $columns = [
              'activity_id' => 'Activity ID',
              'activity' => 'Activity',
              'activity_hours' => ['title' => 'Activity Hours', 'format' => 'hoursminutes'],
              '%_of_total_hours' => ['title' => '% of Total Hours', 'format' => 'percentage'],
              'activity_cost' => ['title' => 'Activity Cost', 'format' => 'currency'],
              '%_of_total_employee_cost' => ['title' => '% of Total Employee Cost', 'format' => 'percentage']
          ];
          foreach ( $columns as $columnID => $columnArgs ) {
              $return[$fullRowHandle . $columnID] = $columnArgs;
          }
          return $return;
      }
    */


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

        return $this->htmlUtility::getTableHTML( [
            'data' => $this->format::formatColumnsValues( $data, $this->getColumns( 'format' ) ),
            'columns' => $this->getColumns(),
            'rows' => $this->getRowArgs()
        ] );
    }
}