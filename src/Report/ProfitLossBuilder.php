<?php


namespace Phoenix\Report;

use Phoenix\Entity\Customer;
use Phoenix\Entity\JobOverPeriodFactory;
use Phoenix\Entity\JobsOverPeriod;
use Phoenix\Report\Archive\ArchiveTableProfitLossJobsInvalid;
use Phoenix\Report\Archive\ArchiveTableProfitLossJobsValid;

/**
 * @author James Jones
 *
 * @property JobOverPeriodFactory $entityFactory
 * @property JobsOverPeriod|null  $entities
 *
 * Class ProfitLossBuilder
 *
 * @package Phoenix\Report
 */
class ProfitLossBuilder extends ReportBuilder
{
    /**
     * @var Customer
     */
    private Customer $customer;

    /**
     * @var bool
     */
    private bool $includeFactoryCosts = false;

    /**
     * @var bool
     */
    private bool $includeFactoryCostsButton = false;

    /**
     * @return $this
     */
    public function includeFactoryCosts(): self
    {
        $this->includeFactoryCosts = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function includeFactoryCostsButton(): self
    {
        $this->includeFactoryCostsButton = true;
        return $this;
    }

    /**
     * @return ProfitLoss
     */
    public function getProfitLoss(): ProfitLoss
    {
        $this->report = $this->getFactory()->getProfitLoss()
            ->setEntities(
                $this->getEntities()
            );

        if ( $this->includeFactoryCosts ) {
            $this->report->includeFactoryCosts();
        }
        if ( $this->includeFactoryCostsButton ) {
            $this->report->includeFactoryCostsButton();
        }
        $this->provisionReportStrings(
            $this->getDefaultEmptyMessage()
        );
        return $this->report;
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function getDefaultEmptyMessage(string $prefix = 'No worked'): string
    {
        return $prefix
            . ' jobs '
            . (isset( $this->customer ) ? 'for customer ' . $this->htmlUtility::getBadgeHTML( $this->customer->name, 'primary' ) . ' ' : '')
            . 'found between'
            . $this->getDateString( 'primary' )
            . ' to report.';
    }

    /**
     * @param Customer|null $customer
     * @return $this
     */
    public function setCustomer(Customer $customer = null): self
    {
        if ( $customer !== null ) {
            $this->customer = $customer;
        }
        $this->resetEntities();
        return $this;
    }

    /**
     * @return JobsOverPeriod
     */
    public function getEntities(): JobsOverPeriod
    {
        // d( $this->dateStart, $this->dateFinish );
        if ( empty( $this->dateStart ) || empty( $this->dateFinish ) ) {
            return new JobsOverPeriod();
        }

        if ( isset( $this->entities ) ) {
            return $this->entities;
        }
        $entities = (new JobsOverPeriod(
            $this->entityFactory->getJobsOverPeriod(
                $this->dateStart,
                $this->dateFinish,
                $this->customer->id ?? null
            ),
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
        $this->report = $this->getFactory()->archiveTables()
            ->getProfitLossJobsValid()
            ->setEntities(
                $this->getEntities()->getCompleteJobs()
            );
        $this->provisionReportStrings(
            $this->getDefaultEmptyMessage(
                'No complete or valid'
            )
        );
        // $this->provisionReport( $report );
        return $this->report;
    }

    /**
     * @return ArchiveTableProfitLossJobsInvalid
     */
    public function getInvalidArchive(): ArchiveTableProfitLossJobsInvalid
    {
        $this->report = $this->getFactory()->archiveTables()
            ->getProfitLossJobsInvalid()
            ->setEntities(
                $this->getEntities()->getIncompleteOrInvalidJobs()
            );
        $this->provisionReportStrings(
            $this->getDefaultEmptyMessage(
                'No incomplete or invalid'
            )
        );

        return $this->report;
    }

    /**
     * @param string $title
     * @return string
     */
    public function annotateTitleWithInputs(string $title = ''): string
    {
        $customer = isset( $this->customer->name ) ? '<small>' . $this->htmlUtility::getBadgeHTML( $this->customer->name ) . '</small> ' : '';
        return parent::annotateTitleWithInputs( $customer . $title );
    }


}