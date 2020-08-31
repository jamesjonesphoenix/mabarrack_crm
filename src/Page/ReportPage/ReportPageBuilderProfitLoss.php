<?php


namespace Phoenix\Page\ReportPage;


use PDO;
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
class ReportPageBuilderProfitLoss extends ReportPageBuilder
{
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

    /**
     * @return array
     */
    public function getJobs(): array
    {
        return (new JobFactory( $this->db, $this->messages ))->getEntities( [
            'id' => [
                'value' => $this->db->run( 'SELECT job FROM shifts WHERE date BETWEEN ? AND ?', [$this->dateStart, $this->dateFinish] )->fetchAll( PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE, 'job' ),
                'operator' => 'IN'
            ]
        ], [
                'shifts' => [
                    'worker' => ['shifts' => false],
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
            ))->init( $jobs, $this->dateStart, $this->dateFinish ) );
        return $this;
    }
}