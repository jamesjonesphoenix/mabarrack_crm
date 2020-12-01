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
        'phone_number' => [
            'title' => 'Phone Number',
            'default' => '&minus;'
        ],
        'number_of_jobs' => [
            'title' => 'Number of Jobs'
        ],
        'profit-loss' => [
            'title' => '',
            'default' => '&minus;'
        ],
    ];

    /**
     * @param Customer $customer
     * @return array
     */
    public function extractEntityData($customer): array
    {
        $numberOfJobs = count( $customer->jobs );

        return [
            'name' => $customer->name,
            'email_address' => $customer->getEmailLink( true ),
            'phone_number' => $customer->getPhoneLink(true),
            'number_of_jobs' => $numberOfJobs,
            'profit-loss' => $numberOfJobs > 0 ? $this->htmlUtility::getViewButton(
                $customer->getProfitLossLink(),
                'View Profit/Loss Report'
            ) : ''
        ];
    }
}