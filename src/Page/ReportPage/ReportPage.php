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
     * @var string|null
     */
    private string $form = '';

    /**
     * @return string
     */
    public function renderBody(): string
    {
        ob_start(); ?>
        <div class="container mb-4 position-relative">
        <div class="row">
        <div class="col">
            <?php echo $this->form; //$this->formHTML;
            ?>
        </div></div>
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

    public function setReportDatesForm(string $form): self
    {
        $this->form = $form;
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