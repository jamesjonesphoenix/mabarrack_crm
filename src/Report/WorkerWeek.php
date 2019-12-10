<?php

namespace Phoenix\Report;

use Phoenix\CurrentUser;
use Phoenix\Format;
use Phoenix\Report;
use Phoenix\DateTime;

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
    public $customerHours;

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
    public $factoryMinutes;

    /**
     * @var
     */
    public $totalPayMinutes;

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
     * @param int $workerID
     * @param string $dateStart
     * @return bool
     */
    public function init(int $workerID = 0, string $dateStart = ''): bool
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
        $dateStart = DateTime::validateDate( $dateStart, true );

        $this->setStartAndFinishDates( $dateStart );


        $shifts = $this->setupShifts();

        if ( empty( $shifts ) ) {
            $this->messages->add( 'No shifts found for this week to report.' );
            return false;
        }
        $this->setupWeeklyTimeRecord();
        $this->setupTimeClockRecord();
        $this->setupCustomerHours();
        $this->setupFactoryHours();

        if ( !empty( $this->totals['amount']['time_paid'] ) ) {
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
        } else {
            $this->setTotal( 0, 'percent_paid_time_value_adding', 'percent' );
            $this->setTotal( 0, 'percent_paid_time_non_chargeable', 'percent' );
            $this->setTotal( 0, 'percent_paid_time_total_recorded', 'percent' );

            $this->setTotal( 0, 'percent_paid_time_factory', 'percent' );
            $this->setTotal( 0, 'percent_paid_time_factory_with_job_number', 'percent' );
            $this->setTotal( 0, 'percent_paid_time_factory_without_job_number', 'percent' );
            $this->setTotal( 0, 'percent_paid_time_lunch', 'percent' );
        }
        return true;


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

        $weekly_time_record = Format::tableValues( $weekly_time_record, [
            'value_adding' => array('type' => 'hoursminutes'),
            'non_chargeable' => array('type' => 'hoursminutes'),
        ] );

        $this->setTotal( $total_value_adding, 'time_value_adding' );
        $this->setTotal( $total_non_chargeable, 'time_non_chargeable' );

        if ( !empty( $this->totals['amount']['total_recorded_time'] ) ) {
            $this->setTotal( $total_value_adding / $this->totals['amount']['total_recorded_time'],
                'percent_time_value_adding', 'percent' );
            $this->setTotal( $total_non_chargeable / $this->totals['amount']['total_recorded_time'],
                'percent_time_non_chargeable', 'percent' );
        } else {
            $this->setTotal( 0, 'percent_time_value_adding', 'percent' );
            $this->setTotal( 0, 'percent_time_non_chargeable', 'percent' );
        }
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
     * @return array|bool
     */
    public function setupTimeClockRecord(): array
    {
        $shifts = $this->getShifts();
        if ( empty( $shifts ) ) {
            return false;
        }
        $timeClockRecord = $this->getDaysArray();
        $totalLunchMinutes = 0;
        foreach ( $this->getShifts() as $shift ) {

            $timeStartedSeconds = strtotime( $shift['time_started'] );
            if ( $timeStartedSeconds < strtotime( $timeClockRecord[$shift['weekday']]['start_time'] ) ) { //if start time is earlier, us this shift's start time
                $timeClockRecord[$shift['weekday']]['start_time'] = date( 'H:i:s', $timeStartedSeconds );
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
                $totalLunchMinutes += $shift['minutes'];
            }
        }

        if ( empty( $timeClockRecord ) ) {
            return false;
        }

        $totalPayMinutes = 0; //total mins to be paid
        $weekend = array('Saturday', 'Sunday');
        foreach ( $timeClockRecord as $weekday => &$dayData ) {

            $dayData['hours'] = $dayData['minutes'];

            if ( $dayData['start_time'] === '23:00:00' ) {
                if ( !in_array( $weekday, $weekend, true ) ) {
                    $dayData['start_time'] = 'On Leave';
                } else {
                    $dayData['start_time'] = '-';
                }
            }
            if ( $dayData['finish_time'] === '00:00:00' ) {
                $dayData['finish_time'] = '-';
            }

            if ( !empty( $this->dateStart ) ) {
                $dayData['date'] = date( 'Y-m-d', strtotime( $this->dateStart . '+ ' . $dayData['ID'] . ' day' ) );
            }
            /*
            if ( empty( $dayData[ 'lunch_start' ] ) )
                $dayData[ 'lunch_start' ] = '-';
            if ( empty( $dayData[ 'lunch_finish' ] ) )
                $dayData[ 'lunch_finish' ] = '-';
            */
            if ( $dayData['lunch_minutes'] === 0 ) {
                $dayData['lunch_start'] = '-';
                $dayData['lunch_finish'] = '-';
            }

            $totalPayMinutes += $dayData['minutes'];
            $dayData['total'] = $totalPayMinutes;
        }
        unset( $dayData );
        $this->setTotal( $totalPayMinutes, 'time_paid' );
        $this->setTotal( $totalLunchMinutes, 'time_lunch' );
        if ( !empty( $this->totals['amount']['total_recorded_time'] ) ) {
            $this->setTotal( $totalPayMinutes / $this->totals['amount']['total_recorded_time'],
                'percent_time_paid', 'percent' );
            $this->setTotal( $totalLunchMinutes / $this->totals['amount']['total_recorded_time'],
                'percent_time_lunch', 'percent' );
        } else {
            $this->setTotal( 0, 'percent_time_paid', 'percent' );
            $this->setTotal( 0, 'percent_time_lunch', 'percent' );
        }
        $timeClockRecord = Format::tableValues( $timeClockRecord, [
            'hours' => array('type' => 'hoursminutes'),
            'total' => array('type' => 'hoursminutes'),
        ] );
        return $this->timeClockRecord = $timeClockRecord;


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
        $jobsList = [];
        foreach ( $shifts as $shift ) {
            if ( empty( $jobsList[$shift['job']] ) ) {
                $jobsList[$shift['job']] = array(
                    'job_ID' => $shift['job'],
                    'customer_ID' => $shift['customer_id'],
                    'customer' => $shift['customer'],
                    'hours_this_week' => $shift['minutes']
                );
            } else {
                $jobsList[$shift['job']]['hours_this_week'] += $shift['minutes'];
            }
        }
        foreach ( $jobsList as &$job ) {
            //$job['hours_this_week'] = $job['hours_this_week'];
            $job['customer_cost'] = $job['hours_this_week'] * $this->getWorker( $this->worker_id )['rate'] / 60;
        }
        unset( $job );
        $jobsList = Format::tableValues( $jobsList, [
                'customer_cost' => array('type' => 'currency'),
                'hours_this_week' => array('type' => 'hoursminutes')]
        );

        return $this->customerHours = $jobsList;

    }

    /**
     * @return mixed
     */
    public
    function getCustomerHours()
    {
        if ( !empty( $this->customerHours ) ) {
            return $this->customerHours;
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
        $factoryShiftsNoJobNumber = [];
        $factoryShiftsWithJobNumber = [];

        $factoryMinutes = 0; //total time spent on factory
        $factoryMinutesWithoutJobNumber = 0;
        $factoryMinutesWithJobNumber = 0;
        foreach ( $shifts as $shift ) {
            if ( $shift['customer'] === 'Factory' && $shift['activity'] !== 'Lunch' ) { //internal job
                if ( empty( $shift['job'] ) ) {
                    $factoryShiftsNoJobNumber[] = $shift;
                    $factoryMinutesWithoutJobNumber += $shift['minutes']; //add minutes of this shift to total
                } elseif ( $shift['job'] > 0 ) {
                    $factoryShiftsWithJobNumber[] = $shift;
                    $factoryMinutesWithJobNumber += $shift['minutes'];
                }
                $factoryMinutes += $shift['minutes'];

            }
        }
        if ( !empty( $factoryShiftsNoJobNumber ) ) {
            $this->factory_hours_no_job_number = $this->setupActivitySummary( $factoryShiftsNoJobNumber );
        }
        if ( !empty( $factoryShiftsWithJobNumber ) ) {
            $this->factory_hours_with_job_number = $this->setupActivitySummary( $factoryShiftsWithJobNumber );
        }
        $this->setTotal( $factoryMinutes,
            'time_factory' );
        $this->setTotal( $factoryMinutesWithJobNumber,
            'time_factory_with_job_number' );
        $this->setTotal( $factoryMinutesWithoutJobNumber,
            'time_factory_without_job_number' );
        if ( !empty( $this->totals['amount']['total_recorded_time'] ) ) {
            $this->setTotal( $factoryMinutes / $this->totals['amount']['total_recorded_time'],
                'percent_time_factory', 'percent' );
            $this->setTotal( $factoryMinutesWithJobNumber / $this->totals['amount']['total_recorded_time'],
                'percent_time_factory_with_job_number', 'percent' );
            $this->setTotal( $factoryMinutesWithoutJobNumber / $this->totals['amount']['total_recorded_time'],
                'percent_time_factory_without_job_number', 'percent' );

            $this->setTotal( $factoryMinutes / $this->totals['amount']['total_recorded_time'], 'factory_time_percentage', 'percent' );
        } else {
            $this->setTotal( 0,
                'percent_time_factory', 'percent' );
            $this->setTotal( 0,
                'percent_time_factory_with_job_number', 'percent' );
            $this->setTotal( 0,
                'percent_time_factory_without_job_number', 'percent' );
            $this->setTotal( 0, 'factory_time_percentage', 'percent' );
        }
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

        $weekDay = date( 'w' );
        $dateStart_timestamp = $weekDay === '5' ? time() : strtotime( 'previous friday' );
        $this->dateStart = date( 'd-m-Y', $dateStart_timestamp );
        $dateFinish_timestamp = $weekDay === '4' ? time() : strtotime( 'next thursday' );
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
            $dayStart = date( 'l', strtotime( $this->dateStart ) );
            $dayStart_index = array_search( $dayStart, $dayList, true );
            for ( $i = 0; $i < $dayStart_index; $i++ ) {
                $dayList[] = array_shift( $dayList );
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
                'customerHours' => $this->getCustomerHours(),
                'factory_hours_with_job_number' => $this->getFactoryHours( true ),
                'factory_hours_no_job_number' => $this->getFactoryHours( false )
            ) );
        }

    }
}