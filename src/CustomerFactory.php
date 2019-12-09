<?php

namespace Phoenix;

/**
 * Class CustomerFactory
 */
class CustomerFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected $className = 'Customer';

    /**
     * @var string
     */
    protected $tableName = 'customers';

    /**
     * Alias for getEntity()
     *
     * @param int $id
     * @return Customer
     */
    public function getCustomer(int $id = 0): Customer
    {
        return $this->getEntity( $id );
    }

    /**
     * Alias for getEntities()
     *
     * @param array $queryArgs
     * @param bool $provision
     * @return Customer[]
     */
    public function getCustomers(array $queryArgs = [], $provision = false): array
    {
        return $this->getEntities( $queryArgs, $provision );
    }

    /**
     * @param array $queryArgs
     * @param bool $provision
     * @return Customer[]
     */
    public function getEntities(array $queryArgs = [], $provision = false): array
    {
        $customers = $this->getClassesFromDBWrapper( $queryArgs );
        if ( !$provision || empty( $customers ) ) {
            return $customers;
        }

        //Add jobs for each customer to Customer
        $jobFactory = new JobFactory( $this->db, $this->messages );
        $customers = $this->addManyToOneEntityProperties( $customers, $jobFactory );

        return $customers;
    }

    /**
     * @return Customer
     */
    protected function instantiateEntityClass(): Entity
    {
        return new Customer( $this->db, $this->messages );
    }

    /**
     * @param array $queryArgs
     * @return Customer[]
     */
    protected function getClassesFromDBWrapper(array $queryArgs = []): array
    {
        return $this->instantiateEntitiesFromDB( $queryArgs );
    }
}