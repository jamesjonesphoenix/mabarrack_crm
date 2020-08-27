<?php


namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\Customer;
use Phoenix\Entity\CustomerFactory;
use Phoenix\Entity\JobFactory;
use Phoenix\Form\CustomerForm;
use Phoenix\Report\Archive\ArchiveTableCustomerJobs;

/**
 * @method Customer getEntity(int $entityID = null)
 *
 * Class DetailPageBuilderCustomer
 *
 * @author James Jones
 * @package Phoenix\DetailPage
 *
 */
class DetailPageBuilderCustomer extends DetailPageBuilder
{
    /**
     * @return CustomerFactory
     */
    protected function getNewEntityFactory(): CustomerFactory
    {
        return new CustomerFactory( $this->db, $this->messages );
    }

    /**
     * @return CustomerForm
     */
    public function getForm(): CustomerForm
    {
        return new CustomerForm(
            $this->HTMLUtility,
            $this->getEntity()
        );
    }

    /**
     * @return $this
     */
    public function addReports(): self
    {
        $customer = $this->getEntity();
        if ( empty( $customer->jobs ) ) {
            return $this;
        }
        $this->page->setReports( [
            'customer_jobs_table' => (new ArchiveTableCustomerJobs(
                $this->HTMLUtility,
                $this->format,
                $this->messages
            ))->setEntities( $customer->jobs, (new JobFactory( $this->db, $this->messages ) )->getNew())
                ->setTitle(
                    ($customer->getNamePossessive() ?? 'Customer') . ' Jobs'
                )
                ->setGroupByForm(
                    $this->getGroupByForm(),
                    $this->groupBy
                )

        ] );
        return $this;
    }
}