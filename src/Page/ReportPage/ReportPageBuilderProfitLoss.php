<?php


namespace Phoenix\Page\ReportPage;


use Phoenix\Entity\JobFactory;
use Phoenix\Entity\ShiftFactory;
use Phoenix\Page\PageBuilder;
use Phoenix\Report\ProfitLoss;

/**
 * Class ReportPageBuilderProfitLoss
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class ReportPageBuilderProfitLoss extends PageBuilder
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

    public function getJobs(): array
    {
        $startDate = '2019-07-01';
        $endDate = '2020-06-30';
        $shiftsFactory = new ShiftFactory( $this->db, $this->messages );
        $shifts = $shiftsFactory->getEntities( [
            'date' => [
                'value' => [
                    'start' => $startDate,
                    'finish' => $endDate
                ],
                'operator' => 'BETWEEN'
            ]
        ] );
        return (new JobFactory( $this->db, $this->messages ))->getEntities( [
            'id' => [
                'value' => $shiftsFactory::getEntityIDs( $shifts, 'job' ),
                'operator' => 'IN'
            ]
        ], [
                'shifts' => [
                    'worker' => true,
                    'activity' => true
                ]
            ]
        );
    }

    /**
     * @return $this
     */
    public function addReport(): self
    {
        $jobs = $this->getJobs();
        $format = $this->format;
        $htmlUtility = $this->HTMLUtility;

        $this->page->setReport(
            (new ProfitLoss(
                $htmlUtility,
                $format,
                $this->messages
            ))->init( $jobs, '2019-07-01', '2020-06-30' ) );
        return $this;
    }

    protected function getNewPage(): ReportPage
    {
        return new ReportPage( $this->HTMLUtility );
    }
}