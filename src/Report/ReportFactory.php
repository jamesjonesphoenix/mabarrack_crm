<?php


namespace Phoenix\Report;


use Phoenix\Format;
use Phoenix\Report\Archive\ArchiveReportFactory;
use Phoenix\Report\Shifts\ShiftsReportFactory;
use Phoenix\URL;
use Phoenix\Utility\HTMLTags;

/**
 * Class ReportFactory
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class ReportFactory extends ReportFactoryBase
{
    /**
     * @var ShiftsReportFactory
     */
    private ShiftsReportFactory $shifts;

    /**
     * @var ArchiveReportFactory
     */
    private ArchiveReportFactory $archiveTables;

    /**
     * Report Factory constructor.
     *
     * @param HTMLTags $htmlUtility
     * @param Format   $format
     * @param URL      $url
     */
    public function __construct(HTMLTags $htmlUtility, Format $format, URL $url)
    {
        parent::__construct(
            $htmlUtility,
            $format,
            $url
        );
        $this->shifts = new ShiftsReportFactory( $htmlUtility, $format, $url );
        $this->archiveTables = new ArchiveReportFactory( $htmlUtility, $format, $url );
    }

    /**
     * @return ShiftsReportFactory
     */
    public function shiftsReports(): ShiftsReportFactory
    {
        return $this->shifts;
    }

    /**
     * @return ArchiveReportFactory
     */
    public function archiveTables(): ArchiveReportFactory
    {
        return $this->archiveTables;
    }

    /**
     * @return JobSummary
     */
    public function getJobSummary(): JobSummary
    {
        return new JobSummary(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
    }

    /**
     * @return ProfitLoss
     */
    public function getProfitLoss(): ProfitLoss
    {
        return new ProfitLoss(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
    }

    /**
     * @return ChooseJobTable
     */
    public function getChooseJobTable(): ChooseJobTable
    {
        return new ChooseJobTable(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
    }

    /**
     * @return ChooseFurnitureTable
     */
    public function getChooseFurnitureTable(): ChooseFurnitureTable
    {
        return new ChooseFurnitureTable(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
    }

    /**
     * @return ChooseActivityTable
     */
    public function getChooseActivityTable(): ChooseActivityTable
    {
        return new ChooseActivityTable(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
    }

}