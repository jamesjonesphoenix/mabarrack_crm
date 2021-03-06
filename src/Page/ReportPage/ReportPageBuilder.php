<?php


namespace Phoenix\Page\ReportPage;


use Phoenix\Form\PeriodicReportForm;
use Phoenix\Messages;
use Phoenix\Page\AdminPageBuilder;
use Phoenix\Page\MenuItems\MenuItemsEntities;
use Phoenix\Page\MenuItems\MenuItemsReports;
use Phoenix\Page\Page;
use Phoenix\PDOWrap;
use Phoenix\Report\Report;
use Phoenix\URL;

/**
 * Class ReportPageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page\ReportPage
 *
 */
abstract class ReportPageBuilder extends AdminPageBuilder
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
    protected string $reportType = '';

    /**
     * @var string
     */
    protected string $title = 'Report';

    /**
     * @param array $inputArgs
     * @return $this
     */
    public function setInputArgs(array $inputArgs = []): self
    {
        $this->setDates( $inputArgs['date_start'] ?? '', $inputArgs['date_finish'] ?? '' )
            ->setReportType( $inputArgs['report'] ?? '' );
        return parent::setInputArgs( $inputArgs );
    }


    /**
     * @return $this
     * @throws \Exception
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage()
            ->setTitle(
                $this->HTMLUtility::getIconHTML(
                    $this->getMenuItems()->getMenuItems()[$this->reportType]['icon']
                ) . ' '
                . $this->title
            )
            ->setHeadTitle( 'Report' );
        $this->addPeriodicReportForm();
        $this->addReports();
        $this->addNavLinks();
        return $this;
    }

    /**
     * @return MenuItemsReports
     */
    public function getMenuItems(): MenuItemsReports
    {
        return new MenuItemsReports();
    }

    /**
     * @return $this
     */
    public function addNavLinks(): self
    {
        $url = $this->getURL();
        foreach ( $this->getMenuItems()->getMenuItems() as $reportType => $menuItem ) {

            if ( ($this->reportType ?? null) !== $reportType ) {
                $navLinks[$reportType] = [
                    'href' => $url->setQueryArg( 'report', $reportType )->write(),
                    'content' => $menuItem['content']
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
        $this->setURL(
            $this->getURL()->setQueryArgs( [
                'date_start' => $dateStart,
                'date_finish' => $dateFinish
            ] )
        );
        return $this;
    }

    /**
     * @param string $reportType
     * @return $this
     */
    public function setReportType(string $reportType = ''): self
    {
        $this->reportType = $reportType;
        return $this;
    }


    /**
     * @return PeriodicReportForm
     */
    public function getPeriodicReportForm(): PeriodicReportForm
    {
        return (new PeriodicReportForm(
            $this->HTMLUtility,
            $this->getURL()
        ))
            ->setDates(
                $this->dateStart,
                $this->dateFinish
            );
    }

    /**
     * @return $this
     */
    public function addPeriodicReportForm(): self
    {
        $this->page->addContent(
            $this->getPeriodicReportForm()->makeFields()->render()
        );
        return $this;
    }

    /**
     * @return Report[]
     */
    abstract public function getReports(): array;

    /**
     * @return $this
     * @throws \Exception
     */
    public function addReports(): self
    {
        $reports = $this->getReports();

        foreach ( $reports as $report ) {

            if ( $report === null ) {
                continue;
            }
            $this->page->addContent(
                $report
                    ->render()
            );
        }
        return $this;
    }

    /**
     * @param string $title
     * @return string
     */
    protected function makeTitle(string $title = ''): string
    {
        return $title;
    }


    /**
     * @param PDOWrap  $db
     * @param Messages $messages
     * @param URL      $url
     * @param string   $reportType
     * @return static|null
     */
    public static function create(PDOWrap $db, Messages $messages, URL $url, string $reportType = ''): ?self
    {
        switch( $reportType ) {
            case 'profit_loss':
                return new ReportPageBuilderProfitLoss( $db, $messages, $url );
            case 'activity_summary':
                return new ReportPageBuilderActivitySummary( $db, $messages, $url );
            case 'worker_week':
                return new ReportPageBuilderWorkerWeek( $db, $messages, $url );
        }
        return null;
    }
}