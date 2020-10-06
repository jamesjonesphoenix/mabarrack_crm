<?php


namespace Phoenix\Page\ReportPage;


use Phoenix\Form\SetReportDatesForm;
use Phoenix\Page\Page;
use Phoenix\Page\PageBuilder;
use Phoenix\Report\PeriodicReport;
use function Phoenix\getScriptFilename;

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
     * @var Page
     */
    protected Page $page;

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
    protected string $reportType;


    /**
     * @return $this
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage()
            ->setTitle( 'CRM Report' )
            ->setHeadTitle( 'Report' );
        $this->addNavLinks();
        $this->addSetReportDatesForm();
        $this->addReport();
        return $this;
    }

    /**
     * @return $this
     */
    public function addNavLinks(): self
    {
        $url = getScriptFilename() . '?page=report&date_start=' . $this->dateStart . '&date_finish=' . $this->dateFinish . '&report=';
        foreach ( [
                      'profit_loss' => 'Profit Loss',
                      'activity_summary' => 'Activities Summary',
                      'billable_vs_non' => 'Billable vs Non-Billable'
                  ] as $reportType => $title ) {
            if ( $this->reportType !== $reportType ) {
                $navLinks[$reportType] = [
                    'url' => $url . $reportType,
                    'text' => $title
                ];
            }
        }
        $this->page->setNavLinks( $navLinks ?? [] );
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
        $this->page->addContent(
            (new SetReportDatesForm(
                $this->HTMLUtility,
                $this->dateStart,
                $this->dateFinish,
                $this->reportType
            ))
                ->makeFields()
                ->render()
        );
        return $this;
    }

    /**
     * @return PeriodicReport|null
     */
    abstract public function getNewReport(): ?PeriodicReport;

    /**
     * @return $this
     */
    public function addReport(): self
    {
        $report = $this->getNewReport()->setDates( $this->dateStart, $this->dateFinish );
        if ( $report === null ) {
            return $this;
        }
        if ( !empty( $this->dateStart ) && !empty( $this->dateFinish ) ) {
            $title = $report->getTitle() . ' '
                . $this->HTMLUtility::getBadgeHTML( date( 'd-m-Y', strtotime( $this->dateStart ) ) )
                . ' to '
                . $this->HTMLUtility::getBadgeHTML( date( 'd-m-Y', strtotime( $this->dateFinish ) ) );
            $report->setTitle( $title );
        }
        $this->page->addContent(
            $report->render()
        );
        return $this;
    }
}