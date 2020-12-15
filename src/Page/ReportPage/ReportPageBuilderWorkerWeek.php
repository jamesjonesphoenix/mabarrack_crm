<?php


namespace Phoenix\Page\ReportPage;

use Phoenix\Entity\Shift;
use Phoenix\Form\PeriodicReportForm;
use Phoenix\Report\Report;


/**
 * Class ReportPageBuilderActivitySummary
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class ReportPageBuilderWorkerWeek extends ReportPageBuilderActivitySummary
{
    /**
     * @var string
     */
    protected string $title = 'Employee Week Report';

    /**
     * @var string
     */
    protected string $userFieldPlaceholder = 'Choose Employee';

    /**
     * @return Report[]
     */
    public function getReports(): array
    {
        $shiftReportBuilder = $this->getReportClient()->getShiftsReportBuilder()->setUser($this->user);

        return [
            $shiftReportBuilder->getTimeClockRecord(),
            $shiftReportBuilder->getWorkerWeekSummary(),
            $shiftReportBuilder->getActivitySummary( $this->sortActivitiesBy, $this->groupActivities ),
            $shiftReportBuilder->getWeekShiftsArchive()
        ];
    }

    /**
     * @param string $dateStart
     * @param string $dateFinish // not used but must be left in to make PHP 7.4 happy
     * @return $this
     */
    public function setDates(string $dateStart = '', string $dateFinish = ''): self
    {
        $reportFactory = $this->getReportClient()->getShiftsReportBuilder()
            ->setDatesForWeek( $dateStart );
        return parent::setDates(
            $reportFactory->getDateStart(),
            $reportFactory->getDateFinish()
        );
    }

    /**
     * @return PeriodicReportForm
     */
    public function getPeriodicReportForm(): PeriodicReportForm
    {
        return parent::getPeriodicReportForm()->disableDateFinish();
    }
}