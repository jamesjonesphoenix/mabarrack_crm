<?php


namespace Phoenix\Report;


use Phoenix\Entity\JobOverPeriodFactory;
use Phoenix\Entity\ShiftFactory;
use Phoenix\Messages;
use Phoenix\PDOWrap;
use Phoenix\Report\Shifts\ShiftsReportBuilder;
use Phoenix\Utility\HTMLTags;

/**
 * Class ReportClient
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class ReportClient
{
    /**
     * @var HTMLTags
     */
    private HTMLTags $htmlUtility;

    /**
     * @var Messages
     */
    private Messages $messages;

    /**
     * @var PDOWrap
     */
    private PDOWrap $db;

    /**
     * @var ReportFactory
     */
    private ReportFactory $factory;

    /**
     * @var ShiftsReportBuilder
     */
    private ShiftsReportBuilder $shiftsReportBuilder;

    /**
     * @var ProfitLossBuilder
     */
    private ProfitLossBuilder $profitLossBuilder;

    public function __construct(ReportFactory $factory, HTMLTags $htmlUtility, PDOWrap $db, Messages $messages)
    {
        $this->factory = $factory;
        $this->htmlUtility = $htmlUtility;
        $this->db = $db;
        $this->messages = $messages;

    }

    /**
     * @return ReportFactory
     */
    public function getFactory(): ReportFactory
    {
        return $this->factory;
    }

    /**
     * @return ShiftsReportBuilder
     */
    public function getShiftsReportBuilder(): ShiftsReportBuilder
    {
        return $this->shiftsReportBuilder ?? ($this->shiftsReportBuilder = new ShiftsReportBuilder(
                $this->getFactory(),
                $this->htmlUtility,
                new ShiftFactory( $this->db, $this->messages )
            ));
    }

    /**
     * @return ProfitLossBuilder
     */
    public function getProfitLossBuilder(): ProfitLossBuilder
    {
        return $this->profitLossBuilder ?? ($this->profitLossBuilder = new ProfitLossBuilder(
                $this->getFactory(),
                $this->htmlUtility,
                new JobOverPeriodFactory( $this->db, $this->messages )
            ));
    }
}