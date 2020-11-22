<?php


namespace Phoenix\Report\Shifts;

use Phoenix\Entity\Shift;

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
     * @var string
     */
    protected string $emptyMessage = 'No job activity to report.';

    /**
     * @var array
     */
    protected array $rowArgs = [
        'total_time' => [
            'class' => 'bg-primary',
        ]
    ];

    /**
     * @var array
     */
    protected array $columns = [
        'activity_id' => 'Activity ID',
        'activity' => 'Activity',
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
     * @var string
     */
    private string $sortBy = 'type';

    /**
     * @var array
     */
    private array $sortableBy = [
        'type' => 'Activities by Type',
        'billable' => 'Value Adding vs. Non-Chargeable',
        'factory' => 'Separated by Factory'
    ];

    /**
     * @param $sortable
     * @return $this
     */
    public function removeSortableOption(string $sortable = ''): self
    {
        if ( array_key_exists( $sortable, $this->sortableBy ) ) {
            unset( $this->sortableBy[$sortable] );
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        //Activities Summary

        if ( empty( $this->title ) ) {
            return $this->sortableBy[$this->sortBy] . '</small>';
        }
        return $this->title;
    }

    /**
     * @return array
     */
    public function getNavLinks(): array
    {
        $url = $this->getURL()->setHash( $this->getID() );
        foreach ( array_keys( $this->sortableBy ) as $sortType ) {
            if ( $this->sortBy !== $sortType ) {
                $links['sort_by_' . $sortType] = [
                    'href' => (clone $url)->setQueryArg( 'sort_activities_by', $sortType )->write(),
                    'content' => 'Sort by ' . ucwords( $sortType ),
                    'class' => 'bg-primary',
                ];
            }
        }
        if ( $this->allowGroupBy ) {
            $links['group_by'] = [
                'href' => (clone $url)->setQueryArg( 'group_activities', !$this->groupedBy )->write(),
                'content' => $this->groupedBy ? 'Display One Table' : 'Display Separate Tables',
                'class' => 'bg-secondary',
            ];
        }
        return array_merge(
            $links ?? [],
            parent::getNavLinks()
        );
    }

    /**
     * @param string $sortBy
     * @return $this
     */
    public function sortBy(string $sortBy = ''): self
    {
        $this->sortBy = $sortBy;
        return $this;
    }


    /**
     * @param Shift $shift
     * @return string
     */
    public function sortShift(Shift $shift): string
    {
        switch( $this->sortBy ) {
            case 'billable':
                return $shift->activity->chargeable ? 'Value Adding Time' : 'Non Chargeable Time';
            case 'factory':
                if ( $shift->job->id === 0 ) {
                    if ( $shift->isLunch() ) {
                        return 'Lunch Time';
                    }
                    return 'Factory Time <small>(No Job Number)</small>';
                }
                /*
                if($shift->job->customer->name !== 'Factory'){
                    d($shift);
                }
                */
                return $shift->job->customer->name === 'Factory' ? 'Factory Time <small>(with job number)</small>' : 'Job Time';
            case 'type':
            default:
                return ($shift->activity->type ?? $shift->activity->category ?? 'Unknown') . ' Time';
        }


    }

    /**
     * @return array
     */
    public function sortShifts(): array
    {

        foreach ( $this->shifts->getAll() as $shift ) {
            $returnShifts[$this->sortShift( $shift )][$shift->id] = $shift;
        }
        if ( count( $returnShifts ?? [] ) === 1 ) {
            $this->disableGroupBy();
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
                        'type' => $groupName
                    ];
                }
                $activitiesSummary[$groupName][$shift->activity->id]['activity_hours'] += $shift->getShiftLength();
                $activitiesSummary[$groupName][$shift->activity->id]['activity_cost'] += $shift->getShiftCost();
            }
        }
        return $activitiesSummary ?? [];
    }

    /**
     * @return array
     */
    protected function extractData(): array
    {
        if ( empty( $this->groupedBy ) ) {
            $this->rowArgs['total_time']['subheader'] = true;
        }


        if ( $this->shifts->getCount() === 0 ) {
            return [];
        }
        $totals = [
            'activity_id' => 'Total',
            'activity' => 'All Activities',
            'activity_hours' => $this->shifts->getTotalWorkerMinutes(),
            'activity_cost' => $this->shifts->getTotalWorkerCost(),
            '%_of_total_hours' => $this->shifts->getTotalWorkerMinutes() > 0 ? '100.0%' : 'N/A',
            '%_of_total_employee_cost' => $this->shifts->getTotalWorkerCost() > 0 ? '100.0%' : 'N/A',
            'type' => 'totals'
        ];
        foreach ( $this->getActivitiesSummary() as $groupName => $activities ) {

            ksort( $activities );
            $groupTotalMinutes = 0;
            $groupTotalCost = 0;
            foreach ( $activities as $activityID => $activity ) {
                $groupedData[$groupName][$groupName . '_' . $activityID] = $activity;

                $groupTotalMinutes += $activity['activity_hours'];
                $groupTotalCost += $activity['activity_cost'];
            }
            $subtotalRowID = 'employee_time_' . strtolower( $groupName );
            $subtotalRow = [
                'activity_id' => 'Subtotal',
                'activity' => $groupName === 'All' ? 'Unspecific Time' : $groupName,
                'activity_hours' => $groupTotalMinutes,
                'activity_cost' => $groupTotalCost,
                'type' => $groupName
            ];
            $groupedData[$groupName][$subtotalRowID] = $subtotalRow;
            if ( !empty( $this->groupedBy ) ) {
                $subtotalRow['type'] = 'totals';
                $groupedData['totals']['totals_' . $subtotalRowID] = $subtotalRow;
            }

            $this->rowArgs[$subtotalRowID] = [
                'class' => 'bg-primary'
            ];
        }

        // $groupedData['totals']['totals'] = $totals;
        // return $this->addPercentOfTotalColumns( $groupedData ?? [] );


        foreach ( $this->addPercentOfTotalColumns( $groupedData ?? [] ) as $groupName => $activities ) {
            $subheaderRow = 'subheader_' . $groupName;
            $this->rowArgs[$subheaderRow] = [
                'subheader' => true,
                'class' => 'text-center'
            ];
            foreach ( $activities as $rowID => $activity ) {
                $returnData[$rowID] = $activity;
            }
        }
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

    public function groupBy(): self
    {
        $this->groupedBy = 'type';
        return $this;
    }
}