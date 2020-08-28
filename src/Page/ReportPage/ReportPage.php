<?php


namespace Phoenix\Page\ReportPage;

use Phoenix\Page\Page;
use Phoenix\Report\Report;

/**
 * Class ReportPage
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class ReportPage extends Page
{
    /**
     * @var Report
     */
    private Report $report;

    /**
     * @return string
     */
    public function renderBody(): string
    {
        ob_start(); ?>
        <div class="container mb-4 position-relative">
            <?php echo ''; //$this->formHTML;
            ?>
        </div>
        <?php
        echo $this->report->render();
        return ob_get_clean();
    }

    /**
     * @param Report $report
     * @return $this
     */
    public function setReport(Report $report): self
    {
        $this->report = $report;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageHeadTitle(): string
    {
        return 'Report';
    }
}