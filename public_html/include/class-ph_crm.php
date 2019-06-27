<?php

final class ph_CRM
{

    protected static $_instance = null;

    protected $db;

    public $class_includes = array(
        'activities',
        'messages',
        'pdo',
        'roles',
        'user',
        'current_user',
        'date_time',
        'report' => array(
            'report',
            'worker_week',
            'job_costing'
        )
    );

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * ph_CRM constructor.
     *
     */
    function __construct() {
        //$this->db = $db;
        return $this->init();
    }

    function init() {
        $this->includes();
        return true;
    }

    /**
     * load up them classes
     *
     * @return bool
     */
    function includes() {
        foreach ( $this->class_includes as $folder => $class_include ) {
            if ( is_array( $class_include ) ) {
                foreach ( $class_include as $class_name ) {
                    $this->class_include( $class_name, $folder );
                }
            } else {
                $this->class_include( $class_include );
            }
        }
        if ( defined( 'DOING_CRON' ) )
            $this->class_include( 'cron_logging' );
        include_once 'functions.php';
        return true;
    }

    function class_include( $class_include, $folder = '' ) {
        if ( !empty( $folder ) )
            $folder = $folder . '/';
        $include_string = $folder . 'class-ph_' . $class_include . '.php';
        if ( file_exists( dirname( __FILE__ ) . '/' . $include_string ) ) {
            include_once $include_string;
            return true;
        }
        return false;
    }
}

/**
 * Main instance of ph_CRM.
 *
 * Returns the main instance of WC to prevent the need to use globals.
 *
 */
function ph_crm() {
    return ph_CRM::instance();
}