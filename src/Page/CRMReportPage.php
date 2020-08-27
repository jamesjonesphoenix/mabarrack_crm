<?php


namespace Phoenix\Page;


use Phoenix\Report\ProfitLoss;

/**
 * Class CRMReportPage
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class CRMReportPage extends Page
{
    /**
     * @var ProfitLoss
     */
    private ProfitLoss $report;

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
     * @param ProfitLoss $report
     * @return $this
     */
    public function setReport(ProfitLoss $report): self
    {
        $this->report = $report;
        return $this;
    }
}