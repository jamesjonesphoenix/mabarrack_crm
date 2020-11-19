<?php

namespace Phoenix\Entity;

use PDO;

/**
 * Class JobOverPeriodFactory
 */
class JobOverPeriodFactory extends JobFactory
{
    /**
     * @return Job
     */
    protected function instantiateEntityClass(): Job
    {
        return new JobOverPeriod( $this->db, $this->messages );
    }

    /**
     * @param string $dateStart
     * @param string $dateFinish
     * @return JobOverPeriod[]
     */
    public function getJobsOverPeriod(string $dateStart = '', string $dateFinish = ''): array
    {
        return $this->getEntities( [
            'id' => [

                'value' => $this->db
                    ->run( 'SELECT job FROM shifts WHERE date BETWEEN ? AND ?', [$dateStart, $dateFinish] )
                    ->fetchAll( PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE, 'job' ),

                 //'value' => [0 => 0, 25590 => 25590, 25587 => 25587, 25588 => 25588],
                'operator' => 'IN'
            ]
        ], [
            'shifts' => [
                'worker' => ['shifts' => false],
                'activity' => true

            ],
            'furniture' => true,
            'customer' => true
        ] );
    }
}