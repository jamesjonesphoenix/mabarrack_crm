<?php


namespace Phoenix\Report;

use Phoenix\Entity\JobOverPeriodFactory;
use Phoenix\Entity\Jobs;
use Phoenix\Entity\JobsOverPeriod;
use Phoenix\Report\Archive\ArchiveTableProfitLossJobsInvalid;
use Phoenix\Report\Archive\ArchiveTableProfitLossJobsValid;

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
     * @return ArchiveTableProfitLossJobsValid
     */
    public function getValidArchive(): ArchiveTableProfitLossJobsValid
    {
        $report = $this->getFactory()->archiveTables()->getProfitLossJobsValid();

        $report->setEntities(
            $this->getEntities()->getCompleteJobs()
        );
        $this->provisionReportStrings( $report );
        // $this->provisionReport( $report );
        return $report;
    }

    /**
     * @return ArchiveTableProfitLossJobsInvalid
     */
    public function getInvalidArchive(): ArchiveTableProfitLossJobsInvalid
    {
        $report = $this->getFactory()->archiveTables()->getProfitLossJobsInvalid();
        $report->setEntities(
            $this->getEntities()->getIncompleteOrInvalidJobs()
        );
        $this->provisionReportStrings( $report );
        return $report;
    }
}