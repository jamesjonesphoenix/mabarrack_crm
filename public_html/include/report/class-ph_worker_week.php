<?php

class ph_Report_Worker_Week extends ph_Report
{

    public $customer_hours;
    public $date_start = '';
    public $date_finish = '';

    public $worker_id;

    public $factory_minutes;
    public $total_pay_minutes;

    public $weekly_time_record = array();
    public $time_clock_record = array();
    public $factory_hours_no_job_number = array();
    public $factory_hours_with_job_number = array();

    /**
     * ph_Report_Worker_Week constructor.
     * @param string $worker_id
     * @param string $date_start
     */
    function __construct( $worker_id = '', $date_start = '' ) {
        parent::__construct();
        return $this->init( $worker_id, $date_start );
    }

    /**
     * @param string $worker_id
     * @param string $date_start
     * @return bool
     */
    function init( $worker_id = '', $date_start = '' ) {
        if ( empty( $worker_id ) ) {
            ph_messages()->add_message( 'Worker ID missing. Can\'t create report.' );
            return false;
        }

        $this->worker_id = ph_validate_number( $worker_id );
        if ( !$this->get_worker( $this->worker_id ) ) {
            ph_messages()->add_message( 'Worker with ID: ' . $this->worker_id . ' doesn\'t exist. Can\'t create report.' );
            return false;
        }
        $date_start = ph_DateTime::validate_date( $date_start, true );

        $this->set_start_and_finish_dates( $date_start );


        $shifts = $this->setup_shifts();

        if ( !empty( $shifts ) ) {
            $this->setup_weekly_time_record();
            $this->setup_time_clock_record();
            $this->setup_customer_hours();
            $this->setup_factory_hours();

            $this->set_a_total( $this->totals[ 'amount' ][ 'time_value_adding' ] / $this->totals[ 'amount' ][ 'time_paid' ],
                'percent_paid_time_value_adding', 'percent' );
            $this->set_a_total( $this->totals[ 'amount' ][ 'time_non_chargeable' ] / $this->totals[ 'amount' ][ 'time_paid' ],
                'percent_paid_time_non_chargeable', 'percent' );
            $this->set_a_total( $this->totals[ 'amount' ][ 'total_recorded_time' ] / $this->totals[ 'amount' ][ 'time_paid' ],
                'percent_paid_time_total_recorded', 'percent' );

            $this->set_a_total( $this->totals[ 'amount' ][ 'time_factory' ] / $this->totals[ 'amount' ][ 'time_paid' ],
                'percent_paid_time_factory', 'percent' );
            $this->set_a_total( $this->totals[ 'amount' ][ 'time_factory_with_job_number' ] / $this->totals[ 'amount' ][ 'time_paid' ],
                'percent_paid_time_factory_with_job_number', 'percent' );
            $this->set_a_total( $this->totals[ 'amount' ][ 'time_factory_without_job_number' ] / $this->totals[ 'amount' ][ 'time_paid' ],
                'percent_paid_time_factory_without_job_number', 'percent' );
            $this->set_a_total( $this->totals[ 'amount' ][ 'percent_time_lunch' ] / $this->totals[ 'amount' ][ 'time_paid' ],
                'percent_paid_time_lunch', 'percent' );

            return true;
        }
        ph_messages()->add_message( 'No shifts found for this week to report.' );
        return false;
    }

    /**
     * @return mixed
     */
    function setup_weekly_time_record() {
        $shifts = $this->get_shifts();
        if ( !empty( $shifts ) ) {
            $weekly_time_record = array();
            $total_value_adding = 0;
            $total_non_chargeable = 0;
            foreach ( $shifts as $shift ) {
                $value_adding = $shift[ 'chargable' ] == 1 ? $shift[ 'minutes' ] : 0;
                $total_value_adding += $value_adding;
                $non_chargeable = $shift[ 'chargable' ] != 1 ? $shift[ 'minutes' ] : 0;
                $total_non_chargeable += $non_chargeable;
                $weekly_time_record[] = array(
                    //'ID' => '<span class="hidden">' . $shift[ 'ID' ] . '</span>',
                    'ID' => $shift[ 'ID' ],
                    'day' => $shift[ 'weekday' ],
                    'date' => date( 'Y-m-d', strtotime( $shift[ 'date' ] ) ),
                    'start_time' => $shift[ 'time_started' ],
                    'finish_time' => $shift[ 'time_finished' ],
                    'job' => $shift[ 'job' ],
                    'customer' => $shift[ 'customer' ],
                    'activity' => $shift[ 'activity' ],
                    'value_adding' => $value_adding,
                    'non_chargeable' => $non_chargeable
                );
            }

            $weekly_time_record = ph_format_table_value( $weekly_time_record, [
                'value_adding' => array( 'type' => 'hoursminutes' ),
                'non_chargeable' => array( 'type' => 'hoursminutes' ),
            ] );

            $this->set_a_total( $total_value_adding, 'time_value_adding' );
            $this->set_a_total( $total_non_chargeable, 'time_non_chargeable' );

            $this->set_a_total( $total_value_adding / $this->totals[ 'amount' ][ 'total_recorded_time' ],
                'percent_time_value_adding', 'percent' );
            $this->set_a_total( $total_non_chargeable / $this->totals[ 'amount' ][ 'total_recorded_time' ],
                'percent_time_non_chargeable', 'percent' );
            return $this->weekly_time_record = $weekly_time_record;
        }
        return false;
    }

    function get_weekly_time_record() {
        if ( !empty( $this->weekly_time_record ) )
            return $this->weekly_time_record;
        return $this->setup_weekly_time_record();
    }

    /**
     * @return array
     */
    function setup_time_clock_record() {
        $shifts = $this->get_shifts();
        if ( !empty( $shifts ) ) {
            $time_clock_record = $this->get_days_array();
            $total_lunch_minutes = 0;
            foreach ( $this->get_shifts() as $shift ) {

                $time_started_seconds = strtotime( $shift[ 'time_started' ] );
                if ( $time_started_seconds < strtotime( $time_clock_record[ $shift[ 'weekday' ] ][ 'start_time' ] ) ) { //if start time is earlier, us this shift's start time
                    $time_clock_record[ $shift[ 'weekday' ] ][ 'start_time' ] = date( "H:i:s", $time_started_seconds );
                }
                $time_finished_seconds = strtotime( $shift[ 'time_finished' ] );
                if ( $time_finished_seconds > strtotime( $time_clock_record[ $shift[ 'weekday' ] ][ 'finish_time' ] ) ) { //if finish time is later, us this shift's finish time
                    $time_clock_record[ $shift[ 'weekday' ] ][ 'finish_time' ] = date( "H:i:s", $time_finished_seconds );
                }

                if ( $shift[ 'activity' ] != 'Lunch' ) {
                    $time_clock_record[ $shift[ 'weekday' ] ][ 'minutes' ] += $shift[ 'minutes' ];
                } elseif ( $shift[ 'activity' ] == 'Lunch' ) {
                    $time_clock_record[ $shift[ 'weekday' ] ][ 'lunch_start' ] = date( 'H:i', strtotime( $shift[ 'time_started' ] ) );
                    $time_clock_record[ $shift[ 'weekday' ] ][ 'lunch_finish' ] = date( 'H:i', strtotime( $shift[ 'time_finished' ] ) );
                    $time_clock_record[ $shift[ 'weekday' ] ][ 'lunch_minutes' ] += $shift[ 'minutes' ];
                    $total_lunch_minutes += $shift[ 'minutes' ];
                }
            }
            if ( !empty( $time_clock_record ) ) {
                $total_pay_minutes = 0; //total mins to be paid
                $weekend = array( 'Saturday', 'Sunday' );
                foreach ( $time_clock_record as $weekday => &$day_data ) {

                    $day_data[ 'hours' ] = $day_data[ 'minutes' ];

                    if ( $day_data[ 'start_time' ] == "23:00:00" ) {
                        if ( !in_array( $weekday, $weekend ) )
                            $day_data[ 'start_time' ] = "On Leave";
                        else
                            $day_data[ 'start_time' ] = "-";
                    }
                    if ( $day_data[ 'finish_time' ] == "00:00:00" ) {
                        $day_data[ 'finish_time' ] = "-";
                    }

                    if ( !empty( $this->date_start ) )
                        $day_data[ 'date' ] = date( 'Y-m-d', strtotime( $this->date_start . '+ ' . $day_data[ 'ID' ] . ' day' ) );
                    /*
                    if ( empty( $day_data[ 'lunch_start' ] ) )
                        $day_data[ 'lunch_start' ] = '-';
                    if ( empty( $day_data[ 'lunch_finish' ] ) )
                        $day_data[ 'lunch_finish' ] = '-';
                    */
                    if ( $day_data[ 'lunch_minutes' ] == 0 ) {
                        $day_data[ 'lunch_start' ] = '-';
                        $day_data[ 'lunch_finish' ] = '-';
                    }

                    $total_pay_minutes += $day_data[ 'minutes' ];
                    $day_data[ 'total' ] = $total_pay_minutes;
                }
                $this->set_a_total( $total_pay_minutes, 'time_paid' );
                $this->set_a_total( $total_lunch_minutes, 'time_lunch' );

                $this->set_a_total( $total_pay_minutes / $this->totals[ 'amount' ][ 'total_recorded_time' ],
                    'percent_time_paid', 'percent' );
                $this->set_a_total( $total_lunch_minutes / $this->totals[ 'amount' ][ 'total_recorded_time' ],
                    'percent_time_lunch', 'percent' );

                $time_clock_record = ph_format_table_value( $time_clock_record, [
                    'hours' => array( 'type' => 'hoursminutes' ),
                    'total' => array( 'type' => 'hoursminutes' ),
                ] );
                return $this->time_clock_record = $time_clock_record;
            }
        }
        return false;
    }

    function get_time_clock_record() {
        if ( !empty( $this->time_clock_record ) )
            return $this->time_clock_record;
        return $this->setup_time_clock_record();
    }

    /**
     * @return mixed
     */
    function setup_customer_hours() {
        $shifts = $this->get_shifts();
        if ( !empty( $shifts ) ) {
            $jobs_list = [];
            foreach ( $shifts as $shift ) {
                if ( empty( $jobs_list[ $shift[ 'job' ] ] ) )
                    $jobs_list[ $shift[ 'job' ] ] = array(
                        'job_ID' => $shift[ 'job' ],
                        'customer_ID' => $shift[ 'customer_id' ],
                        'customer' => $shift[ 'customer' ],
                        'hours_this_week' => $shift[ 'minutes' ]
                    );
                else {
                    $jobs_list[ $shift[ 'job' ] ][ 'hours_this_week' ] += $shift[ 'minutes' ];
                }
            }
            foreach ( $jobs_list as &$job ) {
                $job[ 'hours_this_week' ] = $job[ 'hours_this_week' ];
                $job[ 'customer_cost' ] = $job[ 'hours_this_week' ] * $this->get_worker( $this->worker_id )[ 'rate' ] / 60;
            }

            $jobs_list = ph_format_table_value( $jobs_list, [
                    'customer_cost' => array( 'type' => 'currency' ),
                    'hours_this_week' => array( 'type' => 'hoursminutes' ) ]
            );

            return $this->customer_hours = $jobs_list;
        }
        return false;
    }

    function get_customer_hours() {
        if ( !empty( $this->customer_hours ) )
            return $this->customer_hours;
        return $this->setup_customer_hours();
    }

    function setup_factory_hours( $with_job_number = false ) {
        //factory activity summary
        $shifts = $this->get_shifts();
        if ( !empty( $shifts ) ) {
            $factory_shifts_no_job_number = [];
            $factory_shifts_with_job_number = [];

            $factory_minutes = 0; //total time spent on factory
            $factory_minutes_without_job_number = 0;
            $factory_minutes_with_job_number = 0;
            foreach ( $shifts as $shift ) {
                if ( $shift[ 'customer' ] == 'Factory' && $shift[ 'activity' ] != 'Lunch' ) { //internal job
                    if ( empty( $shift[ 'job' ] ) ) {
                        $factory_shifts_no_job_number[] = $shift;
                        $factory_minutes_without_job_number += $shift[ 'minutes' ]; //add minutes of this shift to total
                    } elseif ( $shift[ 'job' ] > 0 ) {
                        $factory_shifts_with_job_number[] = $shift;
                        $factory_minutes_with_job_number += $shift[ 'minutes' ];
                    }
                    $factory_minutes += $shift[ 'minutes' ];

                }
            }
            if ( !empty( $factory_shifts_no_job_number ) ) {
                $this->factory_hours_no_job_number = $this->setup_activity_summary( $factory_shifts_no_job_number );
            }
            if ( !empty( $factory_shifts_with_job_number ) ) {
                $this->factory_hours_with_job_number = $this->setup_activity_summary( $factory_shifts_with_job_number );
            }
            $this->set_a_total( $factory_minutes,
                'time_factory' );
            $this->set_a_total( $factory_minutes_with_job_number,
                'time_factory_with_job_number' );
            $this->set_a_total( $factory_minutes_without_job_number,
                'time_factory_without_job_number' );

            $this->set_a_total( $factory_minutes / $this->totals[ 'amount' ][ 'total_recorded_time' ],
                'percent_time_factory', 'percent' );
            $this->set_a_total( $factory_minutes_with_job_number / $this->totals[ 'amount' ][ 'total_recorded_time' ],
                'percent_time_factory_with_job_number', 'percent' );
            $this->set_a_total( $factory_minutes_without_job_number / $this->totals[ 'amount' ][ 'total_recorded_time' ],
                'percent_time_factory_without_job_number', 'percent' );

            $this->set_a_total( $factory_minutes / $this->totals[ 'amount' ][ 'total_recorded_time' ], 'factory_time_percentage', 'percent' );

            if ( $with_job_number )
                return $this->factory_hours_with_job_number;

            return $this->factory_hours_with_job_number;
        }
        return false;
    }

    function get_factory_hours( $with_job_number = false ) {
        if ( $with_job_number ) {
            if ( !empty( $this->factory_hours_with_job_number ) )
                return $this->factory_hours_with_job_number;

        } else
            if ( !empty( $this->factory_hours_no_job_number ) )
                return $this->factory_hours_no_job_number;
        return $this->setup_factory_hours( $with_job_number );
    }


    /**
     * @param string $date_start
     * @return bool
     */
    function set_start_and_finish_dates( $date_start = '' ) {
        //get week dates
        if ( !empty( $date_start ) ) {
            $this->date_start = $date_start;
            $this->date_finish = date( "d-m-Y", strtotime( $date_start . ' + 6 days' ) );
        } else {
            if ( date( "w" ) == 5 /*Friday*/ ) {
                $date_start_timestamp = time();
            } else {
                $date_start_timestamp = strtotime( "previous friday" );
            }
            $this->date_start = date( "d-m-Y", $date_start_timestamp );
            if ( date( "w" ) == 4 /*Thursday*/ ) {
                $date_finish_timestamp = time();
            } else {
                $date_finish_timestamp = strtotime( "next thursday" );
            }
            $this->date_finish = date( "d-m-Y", $date_finish_timestamp );
        }
        return true;
    }

    function query_shifts() {
        $shifts = ph_pdo()->run( 'SELECT shifts.ID, shifts.job, shifts.worker, shifts.date, shifts.time_started, shifts.time_finished, shifts.activity, 
    activities.chargable, customers.name as customer, customers.ID as customer_id, users.rate
    FROM shifts
    INNER JOIN jobs ON shifts.job=jobs.ID
    INNER JOIN customers ON jobs.customer=customers.ID
    INNER JOIN activities ON shifts.activity=activities.ID
    INNER JOIN users ON shifts.worker=users.ID
    WHERE shifts.worker = :workerid AND shifts.date >= :datestart AND shifts.date <= :datefinish', [
            'workerid' => $this->worker_id,
            'datestart' => date( 'Y-m-d', strtotime( $this->date_start ) ),
            'datefinish' => date( 'Y-m-d', strtotime( $this->date_finish ) )
        ] )->fetchAll();
        return $shifts;
    }

    /**
     * @return array
     */
    function get_days_array() {
        $daylist = [ 'Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday' ];
        //$dateo->format( 'd-m-Y' );

        if ( !empty( $this->date_start ) ) {
            $day_start = date( 'l', strtotime( $this->date_start ) );
            $day_start_index = array_search( $day_start, $daylist );
            for ( $i = 0; $i < $day_start_index; $i++ ) {
                $shift_day = array_shift( $daylist );
                array_push( $daylist, $shift_day );
            }
        }
        $days = [];
        foreach ( $daylist as $key => $day ) { //initialise array for each day
            $days[ $day ] = array(
                'ID' => $key,
                'day' => $day,
                'start_time' => "23:00:00",
                'finish_time' => "00:00:00",
                'minutes' => 0,
                'lunch_start' => " ",
                'lunch_finish' => " ",
                'lunch_minutes' => 0
            );
        }
        return $days;
    }

    /**
     *
     */
    function output_report() {
        ph_get_template_part( 'report/header/links-' . ph_current_user()->get()->get_role(), array(
            'worker_id' => $this->worker_id,
            'date_next' => date( "d-m-Y", strtotime( $this->date_finish ) ),
            'date_previous' => date( "d-m-Y", strtotime( $this->date_start . ' - 7 days' ) ),
        ) );


        if ( $this->get_shifts() )
            ph_get_template_part( 'report/worker-week/weekly-shifts', array(
                'worker' => $this->get_worker( $this->worker_id ),
                'date_start' => $this->date_start,
                'date_finish' => $this->date_finish,
                'totals' => $this->totals[ 'formatted' ],
                'weekly_time_record' => $this->get_weekly_time_record(),
                'time_clock_record' => $this->get_time_clock_record(),
                'customer_hours' => $this->get_customer_hours(),
                'factory_hours_with_job_number' => $this->get_factory_hours( true ),
                'factory_hours_no_job_number' => $this->get_factory_hours( false )
            ) );

    }
}