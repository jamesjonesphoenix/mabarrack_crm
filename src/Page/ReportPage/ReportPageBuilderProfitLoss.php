<?php


namespace Phoenix\Page\ReportPage;


use Phoenix\Entity\Customer;
use Phoenix\Entity\CustomerFactory;
use Phoenix\Form\PeriodicReportForm;
use Phoenix\Report\Report;

/**
 * Class ReportPageBuilderProfitLoss
 *
 * @author James Jones
 * @package Phoenix\Page
 */
class ReportPageBuilderProfitLoss extends ReportPageBuilder
{
    /**
     * @var string
     */
    protected string $title = 'Profit/Loss Report';

    /**
     * @var Customer|null
     */
    private ?Customer $customer = null;

    /**
     * @var string
     */
    private string $jobType = '';

    /**
     * @var bool
     */
    private bool $exclusiveJobType = false;

    /**
     * @param array $inputArgs
     * @return $this
     */
    public function setInputArgs(array $inputArgs = []): self
    {
        $this->setCustomer( !empty( $inputArgs['customer'] ) ? $inputArgs['customer'] : null );

        $reportBuilder = $this->getReportClient()->getProfitLossBuilder()
            ->setCustomer( $this->customer );

        if ( !empty( $inputArgs['include_factory_costs'] ) ) {

            if ( $this->customer !== null ) {
                $this->messages->add( 'Profit/Loss report will ignore factory costs when a customer is selected.', 'info' );
                $this->setURL(
                    $this->getURL()->setQueryArg( 'include_factory_costs', false )
                );
            } else {
                $reportBuilder->includeFactoryCosts();
            }
        }
        if ( !empty( $inputArgs['job_type'] ) ) {
            $reportBuilder->setJobType( $inputArgs['job_type'] );
            $this->setJobType( $inputArgs['job_type'] );
        }
        if ( !empty( $inputArgs['exclusive'] ) ) {
             $reportBuilder->setExclusiveJobType();
            $this->setExclusiveJobType();
        }

        return parent::setInputArgs( $inputArgs );
    }

    /**
     * @param string $jobType
     * @return $this
     */
    public function setJobType(string $jobType = ''): self
    {
        if ( !empty( $jobType ) ) {
            $this->jobType = $jobType;
        }
        return $this;
    }

    /**
     * @param int|null $customerID
     * @return $this
     */
    public function setCustomer(int $customerID = null): self
    {
        if ( $customerID !== null ) {
            $this->customer = (new CustomerFactory( $this->db, $this->messages ))->getEntity( $customerID, false );
        }

        return $this;
    }

    /**
     * @param string $dateStart
     * @param string $dateFinish
     * @return $this
     */
    public function setDates(string $dateStart = '', string $dateFinish = ''): self
    {
        $this->getReportClient()->getProfitLossBuilder()->setDates( $dateStart, $dateFinish );
        return parent::setDates( $dateStart, $dateFinish );
    }

    /**
     * @return Report[]
     */
    public function getReports(): array
    {
        $builder = $this->getReportClient()->getProfitLossBuilder();
        if ( $this->customer === null ) {
            $builder->includeFactoryCostsButton();
        }
        return [
            $builder->getProfitLoss(),
            $builder->getValidArchive(),
            $builder->getInvalidArchive(),
        ];
    }

    /**
     * @return PeriodicReportForm
     */
    public function getPeriodicReportForm(): PeriodicReportForm
    {
        $form = parent::getPeriodicReportForm();
        if ( $this->customer !== null ) {
            $form->setCustomer( $this->customer );
        }
        if ( !empty( $this->jobType ) ) {
            $form->setJobType( $this->jobType );
        }
        if ( !empty( $this->exclusiveJobType ) ) {
            $form->setExclusiveJobType( );
        }

        return $form
            ->makeCustomerField(
                (new CustomerFactory( $this->db, $this->messages ))->getOptionsArray()
            )
            ->makeJobTypeField();
    }

    /**
     * @return $this
     */
    private function setExclusiveJobType(): self
    {
        $this->exclusiveJobType = true;
        return $this;
    }
}