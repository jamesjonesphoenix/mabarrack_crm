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
     * @param bool|array $provision
     * @return Customer[]
     */
    public function provisionEntities(array $customers = [], $provision = false): array
    {
        if ( !$this->canProvision( $provision, 'jobs' ) ) {
            return $customers;
        }
        //Add jobs for each customer to Customer

        if ( $provision === true ) {
            $provisionJobs = [
                'furniture' => true,
                'shifts' => true,
                'status' => true
            ];
        } else {
            $provisionJobs = $provision['jobs'];
        }
        $provisionJobs['employee'] = false;

        return $this->addManyToOneEntityProperties(
            $customers,
            new JobFactory( $this->db, $this->messages ),
            $provisionJobs
        );
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
        $options = array_column( $this->getAll(), 'name', 'id' );

        $factoryKey = array_search( 'Factory', $options, true );
        $showroomKey = array_search( 'Showroom', $options, true );

        asort( $options );
        return [
                $factoryKey => $options[$factoryKey],
                $showroomKey => $options[$showroomKey]
            ] + $options;
    }
}