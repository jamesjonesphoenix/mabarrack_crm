<?php


namespace Phoenix\Entity;


/**
 * @author James Jones
 * @property JobOverPeriod[] $entities
 * @method JobOverPeriod[] getAll()
 * @method JobOverPeriod getOne(int $id = null)
 *
 * Class JobsOverPeriod
 *
 * @package Phoenix\Entity
 *
 */
class JobsOverPeriod extends Jobs
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
     * @var float|int
     */
    private float $totalSales;

    /**
     * Entities constructor.
     *
     * @param Entity[] $entities
     * @param string   $dateStart
     * @param string   $dateFinish
     */
    public function __construct(array $entities = [], $dateStart = '', $dateFinish = '')
    {
        $this->dateStart = $dateStart;
        $this->dateFinish = $dateFinish;

        parent::__construct( $entities );
        foreach ( $this->entities as $entity ) {
            $entity->setPeriodDates( $dateStart, $dateFinish );
        }
        $totalSales = $this->getTotalSales();
        foreach ( $this->entities as $entity ) {
            $entity->setWeight( $totalSales );
        }
    }

    /**
     * @return float
     */
    public function getFactoryCost(): float
    {
        $factoryJob = $this->getOne( 0 );
        if ( $factoryJob === null ) {
            return 0;
        }
        return $factoryJob->shifts->getShiftsOverTimespan( $this->dateStart, $this->dateFinish )->getTotalWorkerCost();
    }

    /**
     * Calculates sales over period, proportioning value of Job over period
     *
     * @return float
     */
    public function getTotalSales(): float
    {
        if ( isset( $this->totalSales ) ) {
            return $this->totalSales;
        }
        $totalSales = 0;
        foreach ( $this->entities as $job ) {
            if ( empty( $job->healthCheck() ) && empty( $job->checkCompleteAndValid() ) ) {
                $totalSales += $job->salePrice * $job->getPeriodProportion();
            }
        }

        return $this->totalSales = $totalSales;
    }
}