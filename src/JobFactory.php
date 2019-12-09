<?php

namespace Phoenix;

/**
 * Class JobFactory
 */
class JobFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected $className = 'Job';

    /**
     * @var string
     */
    protected $tableName = 'jobs';

    /**
     * Alias for getEntities()
     *
     * @param int $id
     * @return Job
     */
    public function getJob(int $id = 0): Job
    {
        return $this->getEntities( ['ID' => $id], true )[$id];
    }

    /**
     * Alias for getEntities()
     *
     * @param array $queryArgs
     * @param bool $provision
     * @return Job[]
     */
    public function getJobs(array $queryArgs = [], $provision = false): array
    {
        return $this->getEntities( $queryArgs, $provision );
    }

    /**
     * @return Job[]
     */
    public function getActiveJobs(): array
    {
        $queryArgs = [
            'status' => 'jobstat_red',
            'ID' => ['operator' => '!=', 'value' => 0]
        ];
        return $this->getEntities( $queryArgs, true );
    }

    /**
     * @param int $userID
     * @return Job
     */
    public function getLastWorkedJob(int $userID = 0): ?Job
    {
        $shiftFactory = new ShiftFactory( $this->db, $this->messages );
        $lastShift = $shiftFactory->getLastWorkedShift( $userID );

        if ( $lastShift === null ) {
            return null;
        }
        $lastJobID = !empty( $lastShift->job ) ? $lastShift->job : 0;
        $queryArgs = [
            'ID' => $lastJobID
        ];

        return $this->getEntities( $queryArgs, true )[$lastShift->job];
    }

    /**
     * @param array $queryArgs
     * @param bool $provision
     * @return Job[]
     */
    public function getEntities(array $queryArgs = [], $provision = false): array
    {
        $jobs = $this->getClassesFromDBWrapper( $queryArgs );

        if ( !$provision || empty( $jobs ) ) {
            return $jobs;
        }

        //add Furniture to each job
        $furnitureIDs = [];
        foreach ( $jobs as $job ) {
            foreach ( $job->furniture as $item ) {
                $furnitureIDs[$item['ID']] = $item['ID'];
            }
        }
        if ( !empty( $furnitureIDs ) ) {
            $furnitureFactory = new FurnitureFactory( $this->db, $this->messages );
            $furniture = $furnitureFactory->getFurniture( ['id' => ['operator' => 'IN', 'value' => $furnitureIDs]] );
            foreach ( $jobs as &$job ) {
                foreach ( $job->furniture as $furnitureID => $item ) {
                    $jobFurniture[$furnitureID] = $furniture[$furnitureID];
                    $jobFurniture[$furnitureID]->quantity = $item['Quantity'];
                }
                if ( !empty( $jobFurniture ) ) {
                    $job->furniture = $jobFurniture;
                }
            }
            unset( $job );
        }
        //add shifts for each job to Job
        $shiftFactory = new ShiftFactory( $this->db, $this->messages );
        $jobs = $this->addManyToOneEntityProperties( $jobs, $shiftFactory );

        //add customers for each job to Job
        $customerFactory = new CustomerFactory( $this->db, $this->messages );
        $jobs = $this->addOneToOneEntityProperties( $jobs, $customerFactory );

        return $jobs;
    }

    /**
     * @return Job
     */
    protected function instantiateEntityClass(): Entity
    {
        return new Job( $this->db, $this->messages );
    }

    /**
     * @param array $queryArgs
     * @return Job[]
     */
    protected function getClassesFromDBWrapper(array $queryArgs = []): array
    {
        return $this->instantiateEntitiesFromDB( $queryArgs );
    }
}