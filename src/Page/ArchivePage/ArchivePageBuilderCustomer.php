<?php


namespace Phoenix\Page\ArchivePage;


use Phoenix\Entity\CustomerFactory;

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
}