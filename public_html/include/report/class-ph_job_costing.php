<?php

class ph_Report_Job_Costing extends ph_Report
{
    /**
     * @var array
     */
    public $job = array();
    /**
     * @var array
     */
    public $activity_summary = array();

    /**
     * ph_Report_Job_Costing constructor.
     * @param string $job_id
     */
    function __construct($job_id = '' ) {
        parent::__construct();
        return $this->init( $job_id );
    }

    /**
     * @param string $job_id
     * @return bool
     */
    function init($job_id = '' ) {
        if ( empty( $job_id ) ) {
            ph_messages()->add_message( 'Job ID missing. Can\'t create report.' );
            return false;
        }
        $job = $this->get_job( $job_id );

        if ( !empty( $job ) ) {
            $shifts = $this->get_shifts();
            if ( !empty( $shifts ) ) {
                //output activities summary table
                $this->activity_summary = $this->setup_activity_summary();
                //output customer hours
                $total_profit = $job[ 'sale_price' ] - $this->totals[ 'amount' ][ 'employee_cost' ] - $job[ 'material_cost' ] - $job[ 'contractor_cost' ] - $job[ 'spare_cost' ];
                $total_cost = $this->totals[ 'amount' ][ 'employee_cost' ] + $job[ 'material_cost' ] + $job[ 'contractor_cost' ] + $job[ 'spare_cost' ];
                $this->set_a_total( $total_profit, 'total_profit', 'money' );
                $this->set_a_total( $total_cost, 'total_cost', 'money' );
                $this->set_a_total( $total_profit / $total_cost, 'markup', 'percent' );
                $this->set_a_total( $total_profit / $this->get_job()[ 'sale_price' ], 'gross_margin', 'percent' );


                $this->set_a_total( $this->totals[ 'amount' ][ 'employee_cost' ] / $total_cost, 'percent_employee_cost', 'percent' );
                $this->set_a_total( $job[ 'material_cost' ], 'material_cost', 'money' );
                $this->set_a_total( $job[ 'material_cost' ] / $total_cost, 'percent_material_cost', 'percent' );
                $this->set_a_total( $job[ 'contractor_cost' ], 'contractor_cost', 'money' );
                $this->set_a_total( $job[ 'contractor_cost' ] / $total_cost, 'percent_contractor_cost', 'percent' );
                $this->set_a_total( $job[ 'spare_cost' ], 'spare_cost', 'money' );
                $this->set_a_total( $job[ 'spare_cost' ] / $total_cost, 'percent_spare_cost', 'percent' );



                $this->set_a_total( $job[ 'sale_price' ], 'sale_price', 'money' );

                return true;
            }
            ph_messages()->add_message( 'No shifts found for this job. Can\'t create report.' );
            return false;
        }
        ph_messages()->add_message( 'Job not found. Can\'t create report. Is Job ID : "' . $job_id . '" correct?' );
        return false;
    }

    /**
     * @param string $job_id
     * @return array|bool|mixed
     */
    function get_job($job_id = '' ) {
        if ( empty( $this->job ) ) {
            if ( empty( $job_id ) )
                return false;
            $this->job = ph_pdo()->run( 'SELECT jobs.*, customers.name as customer FROM jobs INNER JOIN customers ON jobs.customer=customers.ID WHERE jobs.ID = ?',
                [ $job_id ] )->fetch();
        }
        return $this->job;
    }

    /**
     * @return array|bool
     */
    function query_shifts() {
        $job = $this->get_job();
        if ( !empty( $job ) ) {
            $shifts = ph_pdo()->run( 'SELECT shifts.*, users.name as worker_name, users.rate 
FROM shifts 
INNER JOIN users ON shifts.worker=users.ID 
WHERE shifts.job = ?',
                [ $job[ 'ID' ] ] )->fetchAll();
            return $shifts;
        }
        return false;
    }

    /**
     * @return bool|mixed
     * @throws Exception
     */
    function setup_job_costing() {
        $job_costing = array();
        $shifts = $this->get_shifts();
        if ( !empty( $shifts ) ) {
            foreach ( $shifts as $shift ) {

                // Create a new DateTime object
                $dateo = new DateTime( $shift[ 'date' ] );
                // Modify the date it contains
                $dateo->modify( 'next thursday' );

                $job_costing[] = array(
                    'ID' => $shift[ 'ID' ],
                    'shift_ID' => $shift[ 'ID' ],
                    'worker' => $shift[ 'worker_name' ],
                    'W/ending' => $dateo->format( 'd-m-Y' ),
                    'minutes' => $shift[ 'minutes' ],
                    'activity' => $shift[ 'activity' ],
                    'rate' => $shift[ 'rate' ],
                    'line_item_cost' => $shift[ 'cost' ],
                );
            }
            $job_costing = ph_format_table_value( $job_costing, [
                'minutes' => array( 'type' => 'hoursminutes', 'output_column' => 'hours' ),
                'rate' => array( 'type' => 'currency' ),
                'line_item_cost' => array( 'type' => 'currency' ) ] );
            return $this->job_costing = $job_costing;
        }
        return false;
    }

    /**
     * @return bool|mixed
     * @throws Exception
     */
    function get_job_costing() {
        if ( !empty( $this->job_costing ) )
            return $this->job_costing;
        return $this->setup_job_costing();
    }

    /**
     * @throws Exception
     */
    function output_report() {
        ph_get_template_part( 'report/header/links-admin', array() );
        if ( $this->get_shifts() ) {
            ph_get_template_part( 'report/job-costing/report', array(
                'job' => $this->get_job(),
                'shifts' => $this->get_job_costing(),
                'activities_summary' => $this->activity_summary,
                'totals' => $this->totals[ 'formatted' ],
            ) );
        }
    }
}