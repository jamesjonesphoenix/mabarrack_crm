<?php

namespace Phoenix;

/**
 * Class Roles
 *
 * @package Phoenix
 */
class Roles
{
    /**
     * @var null
     */
    protected static $_instance;
    /*
     *
     * addcomments
     * backup_db (moved out of public_html)
     * othercomment
     * tables_test
     * get_rows
     * search
     * test
     * worker_shifts
     *
     */

    /**
     * @return Roles|null
     */
    public static function instance(): ?Roles
    {
        if ( self::$_instance === null ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @var array
     */
    public $roles = array(
        'admin' => array(
            'file_capabilities' => array(
                'add_entry',
                'customer',
                'delete_job',
                'furniture',
                'index',
                'jcr',
                'job',
                'page',
                'remove_job',
                'settings',
                'shift',
                'tcr',
                'worker',
                'wtr',
                'report',
                'tables_test'
            ),
            'level' => 10
        ),
        'staff' => array(
            'file_capabilities' => array(
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
            ),
            'level' => 1
        ),
        'anyone' => array(
            'file_capabilities' => array(
                'login'
            ),
            'level' => 0
        )
    );


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

    /**
     * @param string $role
     * @return string
     */
    public function getHomePage(string $role = ''): string
    {
        if ( $role == 'admin' ) {
            return 'index.php';
        }
        if ( $role == 'staff' ) {
            return 'worker_enterjob.php';
        }
    }

}