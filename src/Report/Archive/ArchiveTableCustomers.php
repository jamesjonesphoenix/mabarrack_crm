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
            'title' => 'Name'
        ],
        'email_address' => [
            'title' => 'Email Address'
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
            'name' => $customer->name ?? '&minus;',
            'email_address' => $customer->emailAddress ?? 'N/A',
            'number_of_jobs' => count( $customer->jobs ),
        ];
    }
}