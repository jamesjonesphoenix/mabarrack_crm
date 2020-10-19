<?php


namespace Phoenix\Page;

/**
 * @author James Jones
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
    private string $actions;

    /**
     * @var string
     */
    private string $news;

    /**
     * @var string
     */
    private string $workerHoursTable;


    /**
     * @param string $actions
     * @return $this
     */
    public function setActions(string $actions = ''): self
    {
        if ( !empty( $actions ) ) {
            $this->actions = $actions;
        }
        return $this;
    }

    /**
     * @param string $news
     * @return $this
     */
    public function setNews(string $news = ''): self
    {
        if ( !empty( $news ) ) {
            $this->news = $news;
        }
        return $this;
    }

    /**
     * @param string $workerHoursTable
     * @return $this
     */
    public function setWorkerHoursTable(string $workerHoursTable = ''): self
    {
        if ( !empty( $workerHoursTable ) ) {
            $this->workerHoursTable = $workerHoursTable;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function renderDashboard(): string
    {
        ob_start(); ?>
        <div class="container mb-5">
            <div class="row top-worker-page-row">
                <div class="col-md-4">
                    <div class="px-3">
                        <h2>Actions</h2>
                    </div>
                    <div class="grey-bg p-3 clearfix">
                        <?php echo $this->actions ?? 'No actions available.'; ?>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="px-3">
                        <h2>Hours</h2>
                    </div>
                    <div class="grey-bg p-3">
                        <?php echo $this->workerHoursTable ?? 'No worker hours available.'; ?>
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
        <?php return ob_get_clean();
    }

    /**
     * @return string
     */
    public function renderBody(): string
    {
        return $this->renderDashboard() . $this->content;
    }
}