<?php


namespace Phoenix\Page\ReportPage;

use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\Shifts;
use Phoenix\Report\Shifts\ActivitySummary;
use Phoenix\Report\Shifts\BillableVsNon;

/**
 * Class ReportPageBuilderActivitySummary
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class ReportPageBuilderActivitySummary extends ReportPageBuilder
{
    /**
     * @return array
     */
    public function getShifts(): array
    {
        if ( !$this->validateDates() ) {
            return [];
        }
        return (new ShiftFactory( $this->db, $this->messages ))->getEntities( [
            'date' => [
                'value' => [
                    'start' => $this->dateStart,
                    'finish' => $this->dateFinish
                ],
                'operator' => 'BETWEEN'
            ]
        ], [
            'activity' => true,
            'worker' => ['shifts' => false]
        ] );
    }


    /**
     * @return $this
     */
    public function addReport(): self
    {
        $shifts = new Shifts( $this->getShifts() );
        $format = $this->format;
        $htmlUtility = $this->HTMLUtility;

        if ( $this->reportType === 'activity_summary' ) {
            $report = new ActivitySummary(
                $htmlUtility,
                $format
            );
        } elseif ( $this->reportType === 'billable_vs_non' ) {
            $report = new BillableVsNon(
                $htmlUtility,
                $format
            );
        } else {
            return $this;
        }
        $this->page->addContent(
            $report->init( $shifts )->render() );
        return $this;
    }
}