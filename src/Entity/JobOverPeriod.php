<?php


namespace Phoenix\Entity;


/**
 * Class JobOverPeriod
 *
 * @author James Jones
 * @package Phoenix\Entity
 *
 */
class JobOverPeriod extends Job
{
    /**
     * @var string
     */
    private string $datePeriodStart = '';

    /**
     * @var string
     */
    private string $datePeriodFinish = '';

    /**
     * @var float
     */
    private float $proportion;

    /**
     * @var float
     */
    private float $weight = 0;

    /**
     * @param string $datePeriodStart
     * @param string $datePeriodFinish
     * @return $this
     */
    public function setPeriodDates(string $datePeriodStart = '', string $datePeriodFinish = ''): self
    {
        $this->datePeriodStart = $datePeriodStart;
        $this->datePeriodFinish = $datePeriodFinish;
        //  unset( $this->proportion );
        return $this;
    }

    /**
     * @return float
     */
    public function getPeriodProportion(): float
    {
        return $this->proportion ?? (
            $this->proportion = $this->shifts->calculateCompletionOverPeriod( $this->datePeriodStart, $this->datePeriodFinish )
            );
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }


    /**
     * @param float $totalSales
     * @return float
     */
    public function setWeight(float $totalSales): float
    {
        if ( !empty( $totalSales ) ) {
            return $this->weight = $this->salePrice * $this->getPeriodProportion() / $totalSales;
        }
        return $this->weight;
    }
}