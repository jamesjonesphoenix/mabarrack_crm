<?php

namespace Phoenix;

/**
 * Class Roles
 *
 * @package Phoenix
 */
class Roles
{
    /*
     * addcomments
     * backup_db (moved out of public_html)
     * othercomment
     * tables_test
     * get_rows
     * search
     * test
     * worker_shifts
     */

    /**
     * @var array
     */
    protected array $roles = [
        'admin' => [
            'file_capabilities' => [
                'add_entry',
                'archive_page',
                'detail_page',
                'entity',
                'fix-shift-furniture',
                'index',
                'jcr',
                'page',
                'settings',
                'settings-old',
                'tcr',
                'wtr',
                'report',
                'tables_test'
            ],
            'level' => 10
        ],
        'staff' => [
            'file_capabilities' => [
                'chooseactivity',
                'choosefur',
                'choosejob',
                'finishday',
                'finish_day',
                'nextshift',
                'othercomment',
                'report',
                'reports',
                'worker_enterjob',
                'worker',
            ],
            'level' => 1
        ],
        'anyone' => [
            'file_capabilities' => [
                'login'
            ],
            'level' => 0
        ]
    ];

    /**
     * @param string $role
     * @return array
     */
    public function getRoleCapabilities(string $role = ''): array
    {
        if ( empty( $role ) ) {
            $minLevel = 10000;
            foreach ( $this->roles as $roleName => $roleProperties ) {
                if ( $roleProperties['level'] < $minLevel ) {
                    $role = (string)$roleName;
                    $minLevel = $roleProperties['level'];
                }
            }
        }
        if ( !array_key_exists( $role, $this->roles ) ) {
            return [];
        }
        $capabilities = [];
        foreach ( $this->roles as $roleName => $roleProperties ) {
            if ( $roleProperties['level'] <= $this->roles[$role]['level'] ) {
                foreach ( $roleProperties['file_capabilities'] as $capability ) {
                    $filename = $capability . '.php';
                    if ( !in_array( $filename, $capabilities, true ) ) {
                        $capabilities[] = $filename;
                    }
                }
            }
        }
        return $capabilities;
    }



}