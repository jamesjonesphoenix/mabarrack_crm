<?php


namespace Phoenix\Page\ReportPage;

use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\Shifts;
use Phoenix\Page\PageBuilder;
use Phoenix\Report\Shifts\ActivitySummary;

/**
 * Class ReportPageBuilderActivitySummary
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class ReportPageBuilderActivitySummary extends PageBuilder
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
        $this->page->setTitle('Report for Period - ' . '2019-07-01' . ' to ' . '2020-06-30');
        return $this;
    }

    public function getShifts(): array
    {
        $startDate = '2019-07-01';
        $endDate = '2020-06-30';
        return (new ShiftFactory( $this->db, $this->messages ))->getEntities( [
            'date' => [
                'value' => [
                    'start' => $startDate,
                    'finish' => $endDate
                ],
                'operator' => 'BETWEEN'
            ]
        ],[
            'activity' => true,
            'worker' => true
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

    protected function getNewPage(): ReportPage
    {
        return new ReportPage( $this->HTMLUtility );
    }
}