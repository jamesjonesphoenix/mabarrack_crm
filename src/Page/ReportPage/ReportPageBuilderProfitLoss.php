<?php


namespace Phoenix\Page\ReportPage;


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
     * @var bool
     */
    private bool $includeFactoryCosts = false;

    /**
     * @param array $inputArgs
     * @return $this
     */
    public function setInputArgs(array $inputArgs = []): self
    {
        if ( !empty( $inputArgs['include_factory_costs'] ) ) {
            $this->includeFactoryCosts = true;
        }
        return parent::setInputArgs( $inputArgs );
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
        return [
            $builder->getProfitLoss( $this->includeFactoryCosts ),
            $builder->getValidArchive(),
            $builder->getInvalidArchive(),

        ];
    }
}