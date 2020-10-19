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
    protected string $emptyMessage = 'No job activity to report.';

    /**
     * @var array
     */
    protected array $rowArgs = [
        /*
        'subheader_total_time' => [
            'subheader' => true,
            'class' => 'text-center'
        ],
        */
        'total_time' => [
            'class' => 'bg-primary',
            //'class' => 'bg-secondary',
             'subheader' => true
        ]

    ];

    /**
     * @var array
     */
    protected array $columns = [
        'activity_id' => 'Activity ID',
        'activity' => 'Activity',

        /*
        'type' => [
            'title' => 'Type',
        ],
        */

        'activity_hours' => [
            'title' => 'Activity Hours',
            'format' => 'hoursminutes'
        ],
        '%_of_total_hours' => [
            'title' => '% of Total Hours',
            'format' => 'percentage'
        ],
        'activity_cost' => [
            'title' => 'Activity Cost',
            'format' => 'currency'
        ],
        '%_of_total_employee_cost' => [
            'title' => '% of Total Employee Cost',
            'format' => 'percentage'
        ],

    ];

    /**
     * @var bool
     */
    protected bool $printButton = true;

    /**
     * @var Shifts
     */
    protected Shifts $shifts;

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
                        'type' => $shift->activity->type
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
    protected function extractData(): array
    {
        if ( $this->shifts->getCount() === 0 ) {
            return [];
        }
        $totals = [
            'activity_id' => 'Total',
            'activity' => 'All Activities',
            'activity_hours' => $this->shifts->getTotalWorkerMinutes(),
            'activity_cost' => $this->shifts->getTotalWorkerCost(),
            '%_of_total_hours' => $this->shifts->getTotalWorkerMinutes() > 0 ? '100.0%' : 'N/A',
            '%_of_total_employee_cost' => $this->shifts->getTotalWorkerCost() > 0 ? '100.0%' : 'N/A'
        ];
        foreach ( $this->getActivitiesSummary() as $groupName => $activities ) {

            ksort( $activities );
            $groupTotalMinutes = 0;
            $groupTotalCost = 0;
            foreach ( $activities as $activityID => $activity ) {
                $groupedData[$groupName][$activityID] = $activity;

                $groupTotalMinutes += $activity['activity_hours'];
                $groupTotalCost += $activity['activity_cost'];
            }
            $subtotalRow = 'employee_time_' . strtolower( $groupName );
            $groupedData[$groupName][$subtotalRow] = [
                'activity_id' => 'Subtotal',
                'activity' => $groupName === 'All' ? 'Unspecific Time' : $groupName . ' Time',
                'activity_hours' => $groupTotalMinutes,
                'activity_cost' => $groupTotalCost,

            ];

            $this->rowArgs[$subtotalRow] = [
                'class' => 'bg-primary'
            ];


        }



        foreach ( $this->addPercentOfTotalColumns( $groupedData ?? [] ) as $groupName => $activities ) {
            $subheaderRow = 'subheader_' . $groupName;
            $this->rowArgs[$subheaderRow] = [
                 'subheader' => true,
                'class' => 'text-center'
            ];

            // $returnData[$subheaderRow] = [
               // $this->fullRowName => ucwords( $groupName . ' Activities' )
            // ];

            foreach ( $activities as $activityID => $activity ) {
                $returnData[$activityID] = $activity;
            }
        }

        /*
        $returnData['subheader_total_time'] = [
            $this->fullRowName => ucwords( 'Total' )
        ];
        */

        $returnData['total_time'] = $totals;

        return $returnData;
    }

    /**
     * @param $groupedData
     * @return array
     */
    private function addPercentOfTotalColumns($groupedData): array
    {
        if ( $this->shifts->getTotalWorkerMinutes() > 0 ) {
            foreach ( $groupedData as $groupName => &$activities ) {
                foreach ( $activities as $activityID => &$activity ) {
                    $activity['%_of_total_hours'] = $activity['activity_hours'] / $this->shifts->getTotalWorkerMinutes();
                }
            }
        }
        if ( $this->shifts->getTotalWorkerCost() > 0 ) {
            foreach ( $groupedData as $groupName => &$activities ) {
                foreach ( $activities as $activityID => &$activity ) {
                    $activity['%_of_total_employee_cost'] = $activity['activity_cost'] / $this->shifts->getTotalWorkerCost();
                }
            }
        }
        return $groupedData;
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

}