<?php


namespace Phoenix\Report\Shifts;


use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\Shifts;
use Phoenix\Entity\User;
use Phoenix\Report\ReportFactoryBase;
use Phoenix\Report\Shifts\Worker\WorkerWeekSummary;
use Phoenix\Report\Shifts\Worker\WorkerWeekTimeClockRecord;


/**
 * Class ShiftsReportFactory
 *
 * @author James Jones
 * @package Phoenix\Report\Shifts
 *
 */
class ShiftsReportFactory extends ReportFactoryBase
{
    /**
     * @return WorkerWeekTimeClockRecord
     */
    public function getTimeClockRecord(): WorkerWeekTimeClockRecord
    {
        return new WorkerWeekTimeClockRecord(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
    }

    /**
     * @return WorkerWeekSummary
     */
    public function getWorkerWeekSummary(): WorkerWeekSummary
    {
        return new WorkerWeekSummary(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
    }

    /**
     * @param string $sortBy
     * @param bool   $groupSeparateTables
     * @return ActivitySummary
     */
    public function getActivitySummary(string $sortBy = '', bool $groupSeparateTables = false): ActivitySummary
    {
        $report = new ActivitySummary(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
        if ( $groupSeparateTables ) {
            $report->groupBy();
        }
        if ( !empty( $sortBy ) ) {
            $report->sortBy( $sortBy );
        }
        return $report;
    }
}