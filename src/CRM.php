<?php

namespace Phoenix;

/**
 * Class CRM
 */
final class CRM
{

    /**
     * @var null
     */
    protected static $_instance;

    /**
     * @var
     */
    protected $db;

    /**
     * @var array
     */
    public $class_includes = array(
        'activities',
        'messages',
        'pdo',
        'roles',
        'user',
        'currentUser',
        'date_time',
        'report' => array(
            'report',
            'worker_week',
            'job_costing'
        )
    );

    /**
     * @return CRM|null
     */
    public static function instance(): ?CRM
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * CRM constructor.
     *
     */
    private function __construct() {
        //$this->db = $db;
        $this->init();
    }

    /**
     * @return bool
     */
    public function init(): bool
    {
        $this->includes();
        return true;
    }

    /**
     * load up them classes
     *
     * @return bool
     */
    public function includes(): bool
    {
        foreach ( $this->class_includes as $folder => $class_include ) {
            if ( is_array( $class_include ) ) {
                foreach ( $class_include as $class_name ) {
                    $this->class_include( $class_name, $folder );
                }
            } else {
                $this->class_include( $class_include );
            }
        }
        if ( defined( 'DOING_CRON' ) ) {
            $this->class_include('cron_logging');
        }
        return true;
    }

    /**
     * @param $class_include
     * @param string $folder
     * @return bool
     */
    public function class_include($class_include, $folder = '' ): bool
    {
        if ( !empty( $folder ) ) {
            $folder .= '/';
        }
        $include_string = $folder . 'class-ph_' . $class_include . '.php';
        if ( file_exists( __DIR__ . '/' . $include_string ) ) {
            include_once $include_string;
            return true;
        }
        return false;
    }
}