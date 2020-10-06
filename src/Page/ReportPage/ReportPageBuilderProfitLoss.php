<?php


namespace Phoenix\Page\ReportPage;


use PDO;
use Phoenix\Entity\JobFactory;
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
     * @return array
     */
    public function getJobs(): array
    {
        if ( !$this->validateDates() ) {
            return [];
        }
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
     * @return ProfitLoss|null
     */
    public function getNewReport(): ProfitLoss
    {
        return (new ProfitLoss(
            $this->HTMLUtility,
            $this->format
        ))->setJobs( $this->getJobs() );
    }
}