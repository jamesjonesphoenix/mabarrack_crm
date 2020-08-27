<?php

namespace Phoenix\Entity;

/**
 * Class CustomerFactory
 */
class CustomerFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected string $entityName = 'customer';

    /**
     * @return Customer
     */
    protected function instantiateEntityClass(): Customer
    {
        return new Customer( $this->db, $this->messages );
    }

    /**
     * @param Customer[] $customers
     * @param false      $provision
     * @return Customer[]
     */
    public function provisionEntities(array $customers = [], $provision = false): array
    {
        if ( !$this->canProvision( $provision, 'jobs' ) ) {
            return $customers;
        }
        //Add jobs for each customer to Customer
        $jobFactory = new JobFactory( $this->db, $this->messages );

        if ( $provision === true ) {
            $provisionJobs = [
                'furniture' => true,
                'shifts' => true
            ];
        } else {
            $provisionJobs = $provision['jobs'];
        }
        $provisionJobs['worker'] = false;

        return $this->addManyToOneEntityProperties( $customers, $jobFactory, $provisionJobs );
    }
}