<?php


namespace Phoenix\Report\Archive;


use Phoenix\Entity\User;

/**
 * Class ArchiveTableUsers
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableUsers extends ArchiveTable
{
    /**
     * @var array
     */
    protected array $columns = [
        'name' => [
            'title' => 'Name',
            'default' => '&minus;'
        ],
        'pin' => [
            'title' => 'Pin',
            'default' => '&minus;'
        ],
        'rate' => [
            'title' => 'Rate',
            'format' => 'currency'
        ],
        'role' => [
            'title' => 'Role'
        ],
        /*
        'number_of_shifts' => [
            'title' => 'Number Of Shifts'
        ],
        */
        'active' => [
            'title' => 'Active?'
        ],
        'worker_week' => [
            'title' => ''
        ]
    ];

    /**
     * @param User $user
     * @return array
     */
    public function extractEntityData($user): array
    {
        return [
            'name' => $user->name,
            'pin' => $user->pin,
            'rate' => $user->rate,
            'role' => ucwords( $user->role ),
            'number_of_shifts' => $user->shifts->getCount(),
            'active' => $user->active ? 'Yes' : 'No',
            'worker_week' => $this->htmlUtility::getViewButton(
                $user->getWorkerWeekLink(),
                'Worker Week'
            )
        ];
    }
}