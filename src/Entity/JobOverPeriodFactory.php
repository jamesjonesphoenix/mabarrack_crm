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
    public function getJobsOverPeriod(string $dateStart = '', string $dateFinish = '', int $customerID = null): array
    {
        $query = 'SELECT shifts.job FROM shifts INNER JOIN jobs ON jobs.ID=shifts.job WHERE shifts.date BETWEEN ? AND ?';
        $args = [$dateStart, $dateFinish];
        if ( $customerID !== null ) {
            $query .= ' AND jobs.customer = ?';
            $args[] = $customerID;
        }
        /*
        $jobIDs = $this->db->run(
                'SELECT job FROM shifts WHERE date BETWEEN ? AND ?',
                [$dateStart, $dateFinish]
            );
        */
        $jobIDs = $this->db->run(
            $query,
            $args
        )->fetchAll(
            PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE,
            'job'
        );

        // 163
        if ( empty( $jobIDs ) ) {
            return [];
        }
        return $this->getEntities( [
            'id' => [
                'value' => $jobIDs,

                //'value' => [0 => 0, 25590 => 25590, 25587 => 25587, 25588 => 25588],
                'operator' => 'IN'
            ]
        ], [
            'shifts' => [
                'employee' => ['shifts' => false],
                'activity' => true

            ],
            'furniture' => true,
            'customer' => true
        ] );
    }
}