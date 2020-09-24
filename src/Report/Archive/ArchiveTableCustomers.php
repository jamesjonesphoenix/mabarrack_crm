<?php


namespace Phoenix\Report\Archive;


use Phoenix\Entity\Customer;

/**
 * Class ArchiveTableCustomers
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableCustomers extends ArchiveTable
{
    /**
     * @var array
     */
    protected array $columns = [

        'name' => [
            'title' => 'Name',
            'default' => '&minus;'
        ],
        'email_address' => [
            'title' => 'Email Address',
            'default' => '&minus;'
        ],
        'number_of_jobs' => [
            'title' => 'Number of Jobs'
        ],

    ];

    /**
     * @param Customer $customer
     * @return array
     */
    public function extractEntityData($customer): array
    {
        return [
            'name' => $customer->name,
            'email_address' => $customer->emailAddress,
            'number_of_jobs' => count( $customer->jobs ),
        ];
    }
}