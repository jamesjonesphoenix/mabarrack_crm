<?php

namespace Phoenix\Report;

use Phoenix\CurrentUser;
use Phoenix\Report;
use function Phoenix\ph_format_table_value;
use function Phoenix\ph_get_template_part;
use function Phoenix\ph_validate_number;

/**
 * Class WorkerWeek
 *
 * @package Phoenix\Report
 */
class WorkerWeek extends Report
{
    /**
     * @var
     */
    public $customer_hours;

    /**
     * @var string
     */
    public $dateStart = '';

    /**
     * @var string
     */
    public $dateFinish = '';

    /**
     * @var
     */
    public $workerID;

    /**
     * @var
     */
    public $factory_minutes;

    /**
     * @var
     */
    public $total_pay_minutes;

    /**
     * @var array
     */
    public $weekly_time_record = array();

    /**
     * @var array
     */
    public $timeClockRecord = array();

    /**
     * @var array
     */
    public $factory_hours_no_job_number = array();

    /**
     * @var array
     */
    public $factory_hours_with_job_number = array();

    /**
     * @var bool|int
     */
    private $worker_id;


    /**
     * @param string $workerID
     * @param string $dateStart
     * @return bool
     */
    public function init($workerID = '', $dateStart = ''): bool
    {
        if ( empty( $workerID ) ) {
            $this->messages->add( "Worker ID missing. Can't create report." );
            return false;
        }

        $this->worker_id = ph_validate_number( $workerID );
        if ( !$this->getWorker( $this->worker_id ) ) {
            $this->messages->add( 'Worker with ID: ' . $this->worker_id . ' doesn\'t exist. Can\'t create report.' );
            return false;
        }
        $dateStart = \Phoenix\DateTime::validate_date( $dateStart, true );

        $this->setStartAndFinishDates( $dateStart );


        $shifts = $this->setupShifts();

        if ( !empty( $shifts ) ) {
            $this->setupWeeklyTimeRecord();
            $this->setupTimeClockRecord();
            $this->setupCustomerHours();
            $this->setupFactoryHours();

            $this->setTotal( $this->totals['amount']['time_value_adding'] / $this->totals['amount']['time_paid'],
                'percent_paid_time_value_adding', 'percent' );
            $this->setTotal( $this->totals['amount']['time_non_chargeable'] / $this->totals['amount']['time_paid'],
                'percent_paid_time_non_chargeable', 'percent' );
            $this->setTotal( $this->totals['amount']['total_recorded_time'] / $this->totals['amount']['time_paid'],
                'percent_paid_time_total_recorded', 'percent' );

            $this->setTotal( $this->totals['amount']['time_factory'] / $this->totals['amount']['time_paid'],
                'percent_paid_time_factory', 'percent' );
            $this->setTotal( $this->totals['amount']['time_factory_with_job_number'] / $this->totals['amount']['time_paid'],
                'percent_paid_time_factory_with_job_number', 'percent' );
            $this->setTotal( $this->totals['amount']['time_factory_without_job_number'] / $this->totals['amount']['time_paid'],
                'percent_paid_time_factory_without_job_number', 'percent' );
            $this->setTotal( $this->totals['amount']['percent_time_lunch'] / $this->totals['amount']['time_paid'],
                'percent_paid_time_lunch', 'percent' );

            return true;
        }
        $this->messages->add( 'No shifts found for this week to report.' );
        return false;
    }

    /**
     * @return mixed
     */
    public function setupWeeklyTimeRecord()
    {
        $shifts = $this->getShifts();

        if ( empty( $shifts ) ) {
            return false;
        }

        $weekly_time_record = array();
        $total_value_adding = 0;
        $total_non_chargeable = 0;
        foreach ( $shifts as $shift ) {
            $value_adding = $shift['chargable'] === 1 ? $shift['minutes'] : 0;
            $total_value_adding += $value_adding;
            $non_chargeable = $shift['chargable'] !== 1 ? $shift['minutes'] : 0;
            $total_non_chargeable += $non_chargeable;
            $weekly_time_record[] = array(
                //'ID' => '<span class="hidden">' . $shift[ 'ID' ] . '</span>',
                'ID' => $shift['ID'],
                'day' => $shift['weekday'],
                'date' => date( 'Y-m-d', strtotime( $shift['date'] ) ),
                'start_time' => $shift['time_started'],
                'finish_time' => $shift['time_finished'],
                'job' => $shift['job'],
                'customer' => $shift['customer'],
                'activity' => $shift['activity'],
                'value_adding' => $value_adding,
                'non_chargeable' => $non_chargeable
            );
        }

        $weekly_time_record = ph_format_table_value( $weekly_time_record, [
            'value_adding' => array('type' => 'hoursminutes'),
            'non_chargeable' => array('type' => 'hoursminutes'),
        ] );

        $this->setTotal( $total_value_adding, 'time_value_adding' );
        $this->setTotal( $total_non_chargeable, 'time_non_chargeable' );

        $this->setTotal( $total_value_adding / $this->totals['amount']['total_recorded_time'],
            'percent_time_value_adding', 'percent' );
        $this->setTotal( $total_non_chargeable / $this->totals['amount']['total_recorded_time'],
            'percent_time_non_chargeable', 'percent' );
        return $this->weekly_time_record = $weekly_time_record;

    }

    /**
     * @return array|mixed
     */
    public function getWeeklyTimeRecord()
    {
        if ( !empty( $this->weekly_time_record ) ) {
            return $this->weekly_time_record;
        }
        return $this->setupWeeklyTimeRecord();
    }

    /**
     * @return array
     */
    public function setupTimeClockRecord()
    {
        $shifts = $this->getShifts();
        if ( !empty( $shifts ) ) {
            $timeClockRecord = $this->getDaysArray();
            $total_lunch_minutes = 0;
            foreach ( $this->getShifts() as $shift ) {

                $time_started_seconds = strtotime( $shift['time_started'] );
                if ( $time_started_seconds < strtotime( $timeClockRecord[$shift['weekday']]['start_time'] ) ) { //if start time is earlier, us this shift's start time
                    $timeClockRecord[$shift['weekday']]['start_time'] = date( 'H:i:s', $time_started_seconds );
                }
                $time_finished_seconds = strtotime( $shift['time_finished'] );
                if ( $time_finished_seconds > strtotime( $timeClockRecord[$shift['weekday']]['finish_time'] ) ) { //if finish time is later, us this shift's finish time
                    $timeClockRecord[$shift['weekday']]['finish_time'] = date( 'H:i:s', $time_finished_seconds );
                }

                if ( $shift['activity'] !== 'Lunch' ) {
                    $timeClockRecord[$shift['weekday']]['minutes'] += $shift['minutes'];
                } elseif ( $shift['activity'] === 'Lunch' ) {
                    $timeClockRecord[$shift['weekday']]['lunch_start'] = date( 'H:i', strtotime( $shift['time_started'] ) );
                    $timeClockRecord[$shift['weekday']]['lunch_finish'] = date( 'H:i', strtotime( $shift['time_finished'] ) );
                    $timeClockRecord[$shift['weekday']]['lunch_minutes'] += $shift['minutes'];
                    $total_lunch_minutes += $shift['minutes'];
                }
            }
            if ( !empty( $timeClockRecord ) ) {
                $total_pay_minutes = 0; //total mins to be paid
                $weekend = array('Saturday', 'Sunday');
                foreach ( $timeClockRecord as $weekday => &$day_data ) {

                    $day_data['hours'] = $day_data['minutes'];

                    if ( $day_data['start_time'] === '23:00:00' ) {
                        if ( !in_array( $weekday, $weekend, true ) ) {
                            $day_data['start_time'] = 'On Leave';
                        } else {
                            $day_data['start_time'] = '-';
                        }
                    }
                    if ( $day_data['finish_time'] === '00:00:00' ) {
                        $day_data['finish_time'] = '-';
                    }

                    if ( !empty( $this->dateStart ) ) {
                        $day_data['date'] = date( 'Y-m-d', strtotime( $this->dateStart . '+ ' . $day_data['ID'] . ' day' ) );
                    }
                    /*
                    if ( empty( $day_data[ 'lunch_start' ] ) )
                        $day_data[ 'lunch_start' ] = '-';
                    if ( empty( $day_data[ 'lunch_finish' ] ) )
                        $day_data[ 'lunch_finish' ] = '-';
                    */
                    if ( $day_data['lunch_minutes'] === 0 ) {
                        $day_data['lunch_start'] = '-';
                        $day_data['lunch_finish'] = '-';
                    }

                    $total_pay_minutes += $day_data['minutes'];
                    $day_data['total'] = $total_pay_minutes;
                }
                $this->setTotal( $total_pay_minutes, 'time_paid' );
                $this->setTotal( $total_lunch_minutes, 'time_lunch' );

                $this->setTotal( $total_pay_minutes / $this->totals['amount']['total_recorded_time'],
                    'percent_time_paid', 'percent' );
                $this->setTotal( $total_lunch_minutes / $this->totals['amount']['total_recorded_time'],
                    'percent_time_lunch', 'percent' );

                $timeClockRecord = ph_format_table_value( $timeClockRecord, [
                    'hours' => array('type' => 'hoursminutes'),
                    'total' => array('type' => 'hoursminutes'),
                ] );
                return $this->timeClockRecord = $timeClockRecord;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getTimeClockRecord()
    {
        if ( !empty( $this->timeClockRecord ) ) {
            return $this->timeClockRecord;
        }
        return $this->setupTimeClockRecord();
    }

    /**
     * @return mixed
     */
    public function setupCustomerHours()
    {
        $shifts = $this->getShifts();
        if ( !empty( $shifts ) ) {
            return false;

        }
        $jobs_list = [];
        foreach ( $shifts as $shift ) {
            if ( empty( $jobs_list[$shift['job']] ) ) {
                $jobs_list[$shift['job']] = array(
                    'job_ID' => $shift['job'],
                    'customer_ID' => $shift['customer_id'],
                    'customer' => $shift['customer'],
                    'hours_this_week' => $shift['minutes']
                );
            } else {
                $jobs_list[$shift['job']]['hours_this_week'] += $shift['minutes'];
            }
        }
        foreach ( $jobs_list as &$job ) {
            //$job['hours_this_week'] = $job['hours_this_week'];
            $job['customer_cost'] = $job['hours_this_week'] * $this->getWorker( $this->worker_id )['rate'] / 60;
        }

        $jobs_list = ph_format_table_value( $jobs_list, [
                'customer_cost' => array('type' => 'currency'),
                'hours_this_week' => array('type' => 'hoursminutes')]
        );

        return $this->customer_hours = $jobs_list;

    }

    /**
     * @return mixed
     */
    public
    function getCustomerHours()
    {
        if ( !empty( $this->customer_hours ) ) {
            return $this->customer_hours;
        }
        return $this->setupCustomerHours();
    }

    /**
     * @param bool $with_job_number
     * @return array|bool|mixed
     */
    public
    function setupFactoryHours($with_job_number = false)
    {
        //factory activity summary
        $shifts = $this->getShifts();
        if ( empty( $shifts ) ) {
            return false;
        }
        $factory_shifts_no_job_number = [];
        $factory_shifts_with_job_number = [];

        $factory_minutes = 0; //total time spent on factory
        $factory_minutes_without_job_number = 0;
        $factory_minutes_with_job_number = 0;
        foreach ( $shifts as $shift ) {
            if ( $shift['customer'] === 'Factory' && $shift['activity'] !== 'Lunch' ) { //internal job
                if ( empty( $shift['job'] ) ) {
                    $factory_shifts_no_job_number[] = $shift;
                    $factory_minutes_without_job_number += $shift['minutes']; //add minutes of this shift to total
                } elseif ( $shift['job'] > 0 ) {
                    $factory_shifts_with_job_number[] = $shift;
                    $factory_minutes_with_job_number += $shift['minutes'];
                }
                $factory_minutes += $shift['minutes'];

            }
        }
        if ( !empty( $factory_shifts_no_job_number ) ) {
            $this->factory_hours_no_job_number = $this->setupActivitySummary( $factory_shifts_no_job_number );
        }
        if ( !empty( $factory_shifts_with_job_number ) ) {
            $this->factory_hours_with_job_number = $this->setupActivitySummary( $factory_shifts_with_job_number );
        }
        $this->setTotal( $factory_minutes,
            'time_factory' );
        $this->setTotal( $factory_minutes_with_job_number,
            'time_factory_with_job_number' );
        $this->setTotal( $factory_minutes_without_job_number,
            'time_factory_without_job_number' );

        $this->setTotal( $factory_minutes / $this->totals['amount']['total_recorded_time'],
            'percent_time_factory', 'percent' );
        $this->setTotal( $factory_minutes_with_job_number / $this->totals['amount']['total_recorded_time'],
            'percent_time_factory_with_job_number', 'percent' );
        $this->setTotal( $factory_minutes_without_job_number / $this->totals['amount']['total_recorded_time'],
            'percent_time_factory_without_job_number', 'percent' );

        $this->setTotal( $factory_minutes / $this->totals['amount']['total_recorded_time'], 'factory_time_percentage', 'percent' );

        if ( $with_job_number ) {
            return $this->factory_hours_with_job_number;
        }

        return $this->factory_hours_with_job_number;


    }

    /**
     * @param bool $with_job_number
     * @return array|bool|mixed
     */
    public
    function getFactoryHours($with_job_number = false)
    {
        if ( $with_job_number ) {
            if ( !empty( $this->factory_hours_with_job_number ) ) {
                return $this->factory_hours_with_job_number;
            }

        } else
            if ( !empty( $this->factory_hours_no_job_number ) ) {
                return $this->factory_hours_no_job_number;
            }
        return $this->setupFactoryHours( $with_job_number );
    }

    /**
     * @param string $dateStart
     * @return bool
     */
    public
    function setStartAndFinishDates($dateStart = ''): bool
    {
        //get week dates
        if ( !empty( $dateStart ) ) {
            $this->dateStart = $dateStart;
            $this->dateFinish = date( 'd-m-Y', strtotime( $dateStart . ' + 6 days' ) );
            return true;
        }

        if ( date( 'w' ) === '5' /*Friday*/ ) {
            $dateStart_timestamp = time();
        } else {
            $dateStart_timestamp = strtotime( 'previous friday' );
        }
        $this->dateStart = date( 'd-m-Y', $dateStart_timestamp );

        if ( date( 'w' ) === '4' /*Thursday*/ ) {
            $dateFinish_timestamp = time();
        } else {
            $dateFinish_timestamp = strtotime( 'next thursday' );
        }
        $this->dateFinish = date( 'd-m-Y', $dateFinish_timestamp );
        return true;
    }

    /**
     * @return array
     */
    public
    function queryShifts(): array
    {
        $shifts = $this->db->run( 'SELECT shifts.ID, shifts.job, shifts.worker, shifts.date, shifts.time_started, shifts.time_finished, shifts.activity, 
    activities.chargable, customers.name as customer, customers.ID as customer_id, users.rate
    FROM shifts
    INNER JOIN jobs ON shifts.job=jobs.ID
    INNER JOIN customers ON jobs.customer=customers.ID
    INNER JOIN activities ON shifts.activity=activities.ID
    INNER JOIN users ON shifts.worker=users.ID
    WHERE shifts.worker = :workerid AND shifts.date >= :datestart AND shifts.date <= :datefinish', [
            'workerid' => $this->worker_id,
            'datestart' => date( 'Y-m-d', strtotime( $this->dateStart ) ),
            'datefinish' => date( 'Y-m-d', strtotime( $this->dateFinish ) )
        ] )->fetchAll();
        return $shifts ?? [];
    }

    /**
     * @return array
     */
    public
    function getDaysArray(): array
    {
        $dayList = ['Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
        //$dateo->format( 'd-m-Y' );

        if ( !empty( $this->dateStart ) ) {
            $day_start = date( 'l', strtotime( $this->dateStart ) );
            $day_start_index = array_search( $day_start, $dayList, true );
            for ( $i = 0; $i < $day_start_index; $i++ ) {
                $shift_day = array_shift( $dayList );
                $dayList[] = $shift_day;
            }
        }
        $days = [];
        foreach ( $dayList as $key => $day ) { //initialise array for each day
            $days[$day] = array(
                'ID' => $key,
                'day' => $day,
                'start_time' => '23:00:00',
                'finish_time' => '00:00:00',
                'minutes' => 0,
                'lunch_start' => ' ',
                'lunch_finish' => ' ',
                'lunch_minutes' => 0
            );
        }
        return $days;
    }

    /**
     *
     */
    public
    function outputReport(): void
    {
        ph_get_template_part( 'report/header/links-' . CurrentUser::instance()->role, array(
            'worker_id' => $this->worker_id,
            'date_next' => date( 'd-m-Y', strtotime( $this->dateFinish ) ),
            'date_previous' => date( 'd-m-Y', strtotime( $this->dateStart . ' - 7 days' ) ),
        ) );


        if ( $this->getShifts() ) {
            ph_get_template_part( 'report/worker-week/weekly-shifts', array(
                'worker' => $this->getWorker( $this->worker_id ),
                'date_start' => $this->dateStart,
                'date_finish' => $this->dateFinish,
                'totals' => $this->totals['formatted'],
                'weekly_time_record' => $this->getWeeklyTimeRecord(),
                'time_clock_record' => $this->getTimeClockRecord(),
                'customer_hours' => $this->getCustomerHours(),
                'factory_hours_with_job_number' => $this->getFactoryHours( true ),
                'factory_hours_no_job_number' => $this->getFactoryHours( false )
            ) );
        }

    }
}