<?php


namespace Phoenix\Page\ArchivePage;


use Phoenix\Entity\CustomerFactory;
use Phoenix\Report\Archive\ArchiveTableCustomers;

/**
 * Class ArchivePageBuilderCustomer
 *
 * @author James Jones
 * @package Phoenix\ArchivePage
 *
 */
class ArchivePageBuilderCustomer extends ArchivePageBuilder
{
    /**
     * @var array
     */
    protected array $provisionArgs = [
        'jobs' => [
            'furniture' => false,
            'shifts' => false,
            'customer' => false
        ]
    ];

    /**
     * @return CustomerFactory
     */
    protected function getNewEntityFactory(): CustomerFactory
    {
        return new CustomerFactory( $this->db, $this->messages );
    }

    /**
     * @return ArchiveTableCustomers
     */
    protected function getNewArchiveTableReport(): ArchiveTableCustomers
    {
        return new ArchiveTableCustomers($this->HTMLUtility, $this->format);
    }
}