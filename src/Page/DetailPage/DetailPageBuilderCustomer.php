<?php


namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\Customer;
use Phoenix\Entity\CustomerFactory;
use Phoenix\Entity\Entities;
use Phoenix\Entity\JobFactory;
use Phoenix\Form\DetailPageForm\CustomerEntityForm;
use Phoenix\Report\Archive\ArchiveTableJobs;

/**
 * @method Customer getEntity(int $entityID = null)
 * @author James Jones
 * @property  Customer entity
 *
 * Class DetailPageBuilderCustomer
 *
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
     * @return CustomerEntityForm
     */
    public function getForm(): CustomerEntityForm
    {
        return new CustomerEntityForm(
            $this->HTMLUtility,
            $this->getEntity()
        );
    }


    /**
     * @return $this
     * @throws \Exception
     */
    public function addReports(): self
    {
        $customer = $this->getEntity();
        if ( empty( $customer->jobs ) ) {
            return $this;
        }

        // (new JobFactory( $this->db, $this->messages ))->getNew()

        $this->page->addContent( (new ArchiveTableJobs(
            $this->HTMLUtility,
            $this->format,
            $this->getURL()
        ))
            ->setEntities( new Entities( $customer->jobs ) )
            ->setTitle(
                ($customer->getNamePossessive() ?? 'Customer') . ' Jobs'
            )
            ->setGroupByForm(
                $this->getGroupByForm(),
                $this->groupBy
            )
            ->editColumn( [
                'customer',
                'markup',
                'profit_loss',
                'employee_cost'
            ], ['hidden' => true] )

            ->render()
        );
        return $this;
    }
}