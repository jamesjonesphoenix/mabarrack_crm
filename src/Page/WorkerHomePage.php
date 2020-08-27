<?php


namespace Phoenix\Page;

use Phoenix\Report\Report;

/**
 * @author James Jones
 *
 * @property string   $actions
 * @property string   $news
 * @property Report[] $reports
 * @property string   $workerHoursTable
 *
 * Class WorkerPage
 *
 * @package Phoenix\Page
 */
class WorkerHomePage extends Page
{

    /**
     * @var string
     */
    private string $_actions;

    /**
     * @var string
     */
    private string $_news;

    /**
     * @var string
     */
    private string $_workerHoursTable;

    /**
     * @var Report[]
     */
    private array $_reports;


    /**
     * @param string $actions
     * @return string
     */
    public function actions(string $actions = ''): string
    {
        if ( !empty( $actions ) ) {
            $this->_actions = $actions;
        }
        return $this->_actions ?? 'No actions available.';
    }

    /**
     * @param string $news
     * @return string
     */
    public function news(string $news = ''): string
    {
        if ( !empty( $news ) ) {
            $this->_news = $news;
        }
        return $this->_news ?? 'No news right now.';
    }

    /**
     * @param string $workerHoursTable
     * @return string
     */
    public function workerHoursTable(string $workerHoursTable = ''): string
    {
        if ( !empty( $workerHoursTable ) ) {
            $this->_workerHoursTable = $workerHoursTable;
        }
        return $this->_workerHoursTable ?? 'No worker hours available.';
    }

    /**
     * @param Report[] $reports
     * @return Report[]
     */
    public function reports(array $reports = []): array
    {
        if ( !empty( $reports ) ) {
            $this->_reports = $reports;
        }
        return $this->_reports ?? [];
    }

    public function renderBody(): string
    {
        ob_start(); ?>
        <div class="container mb-4">
            <div class="row top-worker-page-row">
                <div class="col-md-4">
                    <div class="px-3">
                        <h2>Actions</h2>
                    </div>
                    <div class="grey-bg p-3 clearfix">
                        <?php echo $this->actions; ?>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="px-3">
                        <h2>Hours</h2>
                    </div>
                    <div class="grey-bg p-3">
                        <?php echo $this->workerHoursTable; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="px-3">
                        <h2>News</h2>
                    </div>
                    <div class="grey-bg p-3">
                        <?php echo $this->news; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php foreach ( $this->reports as $report ) {
        echo $report->render();
    }
        return ob_get_clean();
    }
}