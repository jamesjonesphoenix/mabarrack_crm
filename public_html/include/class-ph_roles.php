<?php

class ph_Roles
{
    protected static $_instance = null;
/*
 *
 * addcomments
 * backup_db (moved out of public_html)
 * othercomment
 * tables_test
 * get_rows
 * search
 * test
 * w_shifts
 *
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
                'report'
            ),
            'level' => 10
        ),
        'staff' => array(
            'file_capabilities' => array(
                'chooseact',
                'choosefur',
                'choosejob',
                'finishday',
                'nextshift',
                'othercomment',
                'report',
                'reports',
                'startlunch',
                'w_enterjob'
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

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function get_role_capabilities( $role = false ) {
        if ( !$role ) {
            $min_level = 10000;
            foreach ( $this->roles as $role_name => $role_properties ) {
                if ( $role_properties[ 'level' ] < $min_level ) {
                    $role = $role_name;
                    $min_level = $role_properties[ 'level' ];
                }
            }
        }
        if ( !array_key_exists( $role, $this->roles ) )
            return false;
        $capabilities = array();
        foreach ( $this->roles as $role_name => $role_properties ) {
            if ( $role_properties[ 'level' ] <= $this->roles[ $role ][ 'level' ] ) {
                $file_capabilities = array();
                foreach ( $role_properties[ 'file_capabilities' ] as $capability ) {
                    $file_capabilities[] = $capability . '.php';
                }
                $capabilities = array_merge( $capabilities, $file_capabilities );
            }
        }
        return $capabilities;
    }
}

/**
 * Main instance of ph_Roles.
 *
 * Returns the main instance of WC to prevent the need to use globals.
 *
 */
function ph_roles() {
    return ph_Roles::instance();
}