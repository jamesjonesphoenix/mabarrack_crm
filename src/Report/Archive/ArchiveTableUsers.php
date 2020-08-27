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
            'title' => 'Name'
        ],
        'pin' => [
            'title' => 'Pin'
        ],
        'rate' => [
            'title' => 'Rate',
            'format' => 'currency'
        ],
        'role' => [
            'title' => 'Role'
        ]
    ];

    /**
     * @param User $user
     * @return array
     */
    public function extractEntityData($user): array
    {
        $pin = !empty( $user->pin ) ? $user->pin : '&minus;';
        return [
            'name' => $user->name ?? '&minus;',
            'pin' => $pin,
            'rate' => $user->rate,
            'role' => ucwords( $user->role )
        ];
    }
}