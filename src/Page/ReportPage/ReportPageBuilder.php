<?php


namespace Phoenix\Page\ReportPage;


use Phoenix\Page\PageBuilder;

/**
 * Class ReportPageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page\ReportPage
 *
 */
class ReportPageBuilder extends PageBuilder
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
     * @return ReportPage
     */
    protected function getNewPage(): ReportPage
    {
        return new ReportPage( $this->HTMLUtility );
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
}