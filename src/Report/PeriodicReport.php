<?php


namespace Phoenix\Report;


/**
 * Class PeriodicReport
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
abstract class PeriodicReport extends Report
{
    /**
     * @var string
     */
    private string $dateStart;

    /**
     * @var string
     */
    private string $dateFinish;

    /**
     * @var bool
     */
    protected bool $printButton = true;

    public function setDates(string $dateStart = '', string $dateFinish = ''): self
    {
        $this->dateStart = $dateStart;
        $this->dateFinish = $dateFinish;
        return $this;
    }
}