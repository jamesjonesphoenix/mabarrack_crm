<?php

namespace Phoenix\Entity;

/**
 * @method Job[] addOneToOneEntityProperties(array $entities = [], EntityFactory $additionFactory = null, string $joinPropertyName = '')
 * @method Job[] instantiateEntitiesFromDB(array $queryArgs = [])
 * @method Job getEntity(int $id = 0) : ?Entity
 *
 * Class JobFactory
 */
class JobFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected string $entityName = 'job';

    /**
     * @return Job
     */
    protected function instantiateEntityClass(): Job
    {
        return new Job( $this->db, $this->messages );
    }

    /**
     * Alias for getEntities()
     *
     * @param int $id
     * @return Job
     */
    public function getJob(int $id = 0): ?Job
    {
        return $this->getEntities( ['ID' => $id], true )[$id] ?? null;
    }

    /**
     * @param int $priority
     * @return Job[]
     */
    public function getActiveJobs(int $priority = 1000000): array
    {
        return $this->getEntities( [
            'status' => 'jobstat_red',
            'ID' => ['operator' => '!=', 'value' => 0],
            'priority' => ['operator' => '<', 'value' => $priority]
        ], true );
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
     * @return Job
     */
    public function getNew(): Job
    {
        $job = $this->instantiateEntityClass();
        $job->dateStarted = date( 'Y-m-d' );
        $job->status = 'jobstat_red';
        //$furnitureFactory = new FurnitureFactory( $this->db, $this->messages );
        //$furniture = $furnitureFactory->getNew();
        //$job->furniture = [$furniture->id => $furniture];
        return $job;
    }

    /**
     * Queries DB for all entities.
     * Returns entities as array to make a <select> form field.
     * Used by Phoenix/EntityForm->getOptionDropdownFieldHTML()
     *
     * @return array [<option> value1 => <option> name1, <option> value2 => <option> name2, ...]
     */
    public function getOptionsArray(): array
    {
        $jobs = $this->getAll();
        foreach ( $jobs as $job ) { //array_column( $jobs, 'id', 'id' ) fails to add id=0 factory job
            $return[$job->id] = $job->id;
        }
        $return[0] = 'Factory';
        return $return ?? [];
    }

    /**
     * @param Job[] $jobs
     * @param false $provision
     * @return Job[]
     */
    public function provisionEntities(array $jobs = [], $provision = false): array
    {
        if ( $this->canProvision( $provision, 'furniture' ) ) { //Add Furniture to each job. Similar to addManyToOneEntityProperties() but furniture is a little more unique
            $jobs = $this->addFurniture( $jobs );
            $jobs = $this->addFurnitureNames( $jobs );
        }

        if ( $this->canProvision( $provision, 'shifts' ) ) { //Add shifts for each job to Job
            $shiftFactory = new ShiftFactory( $this->db, $this->messages );

            /*Prevent shiftFactory from provisioning job otherwise we waste time on query*/
            if ( $provision === true || $provision['shifts'] === true ) {
                $provisionShifts = [
                    'worker' => true,
                    'activity' => true,
                    'furniture' => true
                ];
            } else {
                $provisionShifts = $provision['shifts'] ?? $provision;
            }
            $provisionShifts['job'] = false;
            $provisionShifts = $provision['shifts'] ?? $provision;
            $jobs = $this->addManyToOneEntityProperties( $jobs, $shiftFactory, $provisionShifts );
        }

        if ( $this->canProvision( $provision, 'customer' ) ) { //Add customers for each job to Job
            $customerFactory = new CustomerFactory( $this->db, $this->messages );
            $jobs = $this->addOneToOneEntityProperties( $jobs, $customerFactory );
        }
        return $jobs;
    }

    /**
     * Converts furniture JSON string consisting of ID and quantity into Furniture instances in array of Job instances
     *
     * @param Job[] $jobs
     * @return Job[]
     */
    public function addFurniture(array $jobs = []): array
    {
        $furnitureFactory = new FurnitureFactory( $this->db, $this->messages );

        foreach ( $jobs as &$job ) {
            $furnitureArray = json_decode( $job->furniture, true );
            if ( empty( $furnitureArray ) ) {
                $job->furniture = [];
                continue;
            }
            $jobFurniture = [];
            foreach ( $furnitureArray as $item ) {

                $furnitureID = key( $item );
                $furnitureQuantity = array_shift( $item );

                $furnitureInstance = $furnitureFactory->getNew();
                $furnitureInstance->id = $furnitureID;
                $furnitureInstance->quantity = $furnitureQuantity;

                $jobFurniture[$furnitureID] = $furnitureInstance;
            }
            if ( !empty( $jobFurniture ) ) {
                $job->furniture = $jobFurniture;
            }

        }
        return $jobs;
    }

    /**
     *
     * @param Job[] $jobs
     * @return Job[]
     */
    public function addFurnitureNames(array $jobs = []): array
    {
        $furnitureFactory = new FurnitureFactory( $this->db, $this->messages );
        $furnitureIDs = [];
        foreach ( $jobs as $job ) {
            if ( !empty( $job->furniture ) && is_array( $job->furniture ) ) {
                foreach ( $job->furniture as $furniture ) {
                    $furnitureIDs[$furniture->id] = $furniture->id;
                }
            }
        }
        if ( empty( $furnitureIDs ) ) {
            return $jobs;
        }
        $furnitureInstances = $furnitureFactory->getEntities( ['id' => ['operator' => 'IN', 'value' => $furnitureIDs]] );

        foreach ( $jobs as $job ) {
            if ( !empty( $job->furniture ) && is_array( $job->furniture ) ) {
                foreach ( $job->furniture as $furnitureID => $furniture ) {
                    $furniture->name = $furnitureInstances[$furniture->id]->name ?? '';
                    $furniture->namePlural = $furnitureInstances[$furniture->id]->namePlural ?? '';
                    /*
                    if ( empty( $furniture->name ) && CurrentUser::instance()->role === 'admin' ) {
                        $this->addError(
                            '<a href="'. $job->getLink() .'">Job ID: <strong>' . $job->id . '</a></strong> includes a furniture with ID: <strong>' . $furniture->id . "</strong> that doesn't exist in the database."
                        );
                    }
                    */
                }
            }
        }

        return $jobs;
    }
}