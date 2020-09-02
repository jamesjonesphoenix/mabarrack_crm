<?php


namespace Phoenix\Page\ReportPage;


use Phoenix\Form\SetReportDatesForm;
use Phoenix\Page\PageBuilder;

/**
 * Class ReportPageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page\ReportPage
 *
 */
abstract class ReportPageBuilder extends PageBuilder
{
    /**
     * @var ReportPage
     */
    protected ReportPage $page;

    /**
     * @var string
     */
    protected string $dateStart = '';

    /**
     * @var string
     */
    protected string $dateFinish = '';

    /**
     * @var string
     */
    private string $reportType;

    /**
     * @return ReportPage
     */
    protected function getNewPage(): ReportPage
    {
        return new ReportPage( $this->HTMLUtility );
    }

    /**
     * @return $this
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage();
        $this->addReport();
        $this->addSetReportDatesForm();
        $this->page->setTitle( 'Report for Period - ' . date( 'd-m-Y', strtotime( $this->dateStart ) ) . ' to ' . date( 'd-m-Y', strtotime( $this->dateFinish ) ) );
        return $this;
    }

    /**
     * @param string $dateStart
     * @param string $dateFinish
     * @return $this
     */
    public function setDates(string $dateStart = '', string $dateFinish = ''): self
    {

        $this->dateStart = $dateStart;
        $this->dateFinish = $dateFinish;

        return $this;
    }

    /**
     * @return bool
     */
    public function validateDates(): bool
    {
        if ( empty( $this->dateStart ) ) {
            if ( empty( $this->dateFinish ) ) {
                $this->messages->add( '<strong>Error:</strong> No dates were set for report.' );
                return false;
            }
            $this->messages->add( '<strong>Error:</strong> Start date was not set for report.' );
            return false;
        }
        if ( empty( $this->dateFinish ) ) {
            $this->messages->add( '<strong>Error:</strong> End date was not set for report.' );
            return false;
        }
        $differenceDays = (integer)(date_diff( date_create( $this->dateStart ), date_create( $this->dateFinish ) ))->format( '%R%a' );
        if ( $differenceDays < 0 ) {
            $this->messages->add( "<strong>Error:</strong> Can't generate report because end date is before start date." );
            return false;
        }
        if ( $differenceDays === 0 ) {
            $this->messages->add( "<strong>Error:</strong> Can't generate report because start date and end date are identical." );
            return false;
        }
        return true;
    }

    public function setReportType(string $reportType = ''): self
    {
        $this->reportType = $reportType;
        return $this;
    }


    /**
     * @return $this
     */
    public function addSetReportDatesForm(): self
    {
        $this->page->setReportDatesForm(
            (new SetReportDatesForm( $this->HTMLUtility, $this->dateStart, $this->dateFinish, $this->reportType ))->makeFields()->render()
        );
        return $this;
    }
}