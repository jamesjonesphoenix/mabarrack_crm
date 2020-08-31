<?php


namespace Phoenix\Page\ReportPage;

use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\Shifts;
=use Phoenix\Report\Shifts\ActivitySummary;

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
     * @var ReportPage
     */
    protected ReportPage $page;

    /**
     * @return $this
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage();
        $this->addReport();
        $this->page->setTitle( 'Report for Period - ' . date( 'd-m-Y', strtotime( $this->dateStart ) ) . ' to ' . date( 'd-m-Y', strtotime( $this->dateFinish ) ) );
        return $this;
    }

    public function getShifts(): array
    {
        return (new ShiftFactory( $this->db, $this->messages ))->getEntities( [
            'date' => [
                'value' => [
                    'start' => $this->dateStart,
                    'finish' => $this->dateFinish
                ],
                'operator' => 'BETWEEN'
            ]
        ],[
            'activity' => true,
            'worker' => ['shifts' => false]
        ] );
    }

    /**
     * @return $this
     */
    public function addReport(): self
    {
        $shifts = new Shifts($this->getShifts());
        $format = $this->format;
        $htmlUtility = $this->HTMLUtility;

        $this->page->setReport(
            (new ActivitySummary(
                $htmlUtility,
                $format,
                $this->messages
            ))->init( $shifts ) );
        return $this;
    }
}