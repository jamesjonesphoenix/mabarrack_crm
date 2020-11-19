<?php


namespace Phoenix\Report;

use Phoenix\Entity\JobOverPeriodFactory;
use Phoenix\Entity\Jobs;
use Phoenix\Entity\JobsOverPeriod;
use Phoenix\Report\Archive\ArchiveTableJobsProfitLoss;

/**
 * @author James Jones
 *
 * @property JobOverPeriodFactory $entityFactory
 * @property Jobs|null            $entities
 *
 * Class ProfitLossBuilder
 *
 * @package Phoenix\Report
 */
class ProfitLossBuilder extends ReportBuilder
{
    /**
     * @param bool $includeFactoryCosts
     * @return ProfitLoss
     */
    public function getProfitLoss(bool $includeFactoryCosts = false): ProfitLoss
    {
        $report = $this->getFactory()->getProfitLoss();
        if ( $includeFactoryCosts ) {
            $report->includeFactoryCosts();
        }

        $this->provisionReport( $report );

        return $report;
    }

    /**
     * @return Jobs
     */
    public function getEntities(): Jobs
    {
        // d( $this->dateStart, $this->dateFinish );
        if ( empty( $this->dateStart ) || empty( $this->dateFinish ) ) {
            return new JobsOverPeriod();
        }

        if ( isset( $this->entities ) ) {
            return $this->entities;
        }

        $entities = (new JobsOverPeriod(
            $this->entityFactory->getJobsOverPeriod( $this->dateStart, $this->dateFinish ),
            $this->dateStart,
            $this->dateFinish
        ));
        // $entities->getProportionsAndWeightsOverPeriod($this->dateStart, $this->dateFinish);
        return $this->entities = $entities;

    }

    /**
     * @param bool $includeFactoryCosts
     * @return ArchiveTableJobsProfitLoss
     */
    public function getArchive(bool $includeFactoryCosts = false): ArchiveTableJobsProfitLoss
    {
        $report = $this->getFactory()->archiveTables()->getJobsProfitLoss();
        $this->provisionReport( $report );
        return $report;
    }
}