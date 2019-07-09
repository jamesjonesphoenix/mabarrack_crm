<?php


/**
 * Class ph_Report
 */
class ph_Report
{

    /**
     * @var ph_Activities
     */
    public $activities;
    /**
     * @var
     */
    public $shifts;
    /**
     * @var array
     */
    public $workers = array();

    /**
     * @var array
     */
    public $totals = array();

    /**
     * ph_Report constructor.
     * @param string $worker_id
     * @param string $date_start
     */
    function __construct($worker_id = '', $date_start = '')
    {

        $this->activities = new ph_Activities();
        return true;
        //return $this->init( $worker_id, $date_start );
    }

    /**
     * @param $shifts
     * @param int $total_mins
     * @param int $total_employee_cost
     * @return mixed
     */
    function setup_activity_summary($shifts = array())
    {

        if (empty($shifts)) {
            $shifts = $this->get_shifts();
            if (empty($shifts))
                return false;
        }

        $activities_summary = array();
        $activities = new ph_Activities();

        foreach ($shifts as $key => $shift) {
            if (empty($activities_summary[$shift['activity']])) {
                //$activity_id = $activities->getID( $shift[ 'activity' ] );
                $activities_summary[$shift['activity']] = array(
                    'activity_ID' => $shift['activity_ID'] ? $shift['activity_ID'] : 'N/A',
                    'activity' => $shift['activity'],
                    'activity_hours' => $shift['minutes'],
                    'activity_cost' => $shift['cost'],
                );
            } else {
                $activities_summary[$shift['activity']]['activity_hours'] += $shift['minutes'];
                $activities_summary[$shift['activity']]['activity_cost'] += $shift['cost'];
            }
        }
        $format_columns = array(
            'activity_hours' => array('type' => 'hoursminutes'),
            'activity_cost' => array('type' => 'currency')
        );
        if (!empty($this->totals['amount']['total_recorded_time']) && $this->totals['amount']['total_recorded_time'] > 0) {
            foreach ($activities_summary as &$activity) {
                $activity['%_of_total_hours'] = $activity['activity_hours'] / $this->totals['amount']['total_recorded_time'];
            }
            $format_columns['%_of_total_hours'] = array('type' => 'percentage');
        }
        if (!empty($this->totals['amount']['employee_cost']) && $this->totals['amount']['employee_cost'] > 0) {
            foreach ($activities_summary as &$activity) {
                $activity['%_of_total_employee_cost'] = $activity['activity_cost'] / $this->totals['amount']['employee_cost'];
            }
            $format_columns['%_of_total_employee_cost'] = array('type' => 'percentage');
        }

        if (!empty($this->totals['amount']['time_paid'])) {
            foreach ($activities_summary as &$activity) {
                $activity['%_of_hours_paid'] = $activity['activity_hours'] / $this->totals['amount']['time_paid'];
            }
            $format_columns['%_of_hours_paid'] = array('type' => 'percentage');
        }

        return ph_format_table_value($activities_summary, $format_columns);
    }

    /**
     * @return mixed
     */
    function get_shifts()
    {
        if (!empty($this->shifts))
            return $this->shifts;
        return $this->setup_shifts();
    }

    /**
     * @param $worker_id
     * @return bool|mixed
     */
    function get_worker($worker_id)
    {
        if (empty($worker_id))
            return false;
        if (!empty($this->workers[$worker_id]))
            return $this->workers[$worker_id];
        $this->workers[$worker_id] = ph_pdo()->get_row(
            'users',
            array('ID', 'name', 'rate'),
            array('ID' => $worker_id)
        );
        if (!empty($this->workers[$worker_id]))
            return $this->workers[$worker_id];
        return false;
    }


    /**
     * @return mixed
     */
    function setup_shifts()
    {
        $shifts = $this->query_shifts();
        if (!empty($shifts)) {
            $total_recorded_minutes = 0;
            $total_employee_cost = 0;
            $warning_string = '<strong>Not recorded</strong>';
            foreach ($shifts as &$shift) {
                if (!empty($shift['time_started']) && !empty($shift['time_finished']))
                    $time_difference = ph_DateTime::time_difference($shift['time_started'], $shift['time_finished']);
                else {
                    if (empty($shift['time_started'])) {
                        $shift['time_started'] = $warning_string;
                        $warning_type = 'start time';
                    }
                    if (empty($shift['time_finished'])) {
                        $shift['time_finished'] = $warning_string;
                        $warning_type = 'finish time';
                    }
                    ph_messages()->add_message('No ' . $warning_type . ' was recorded for <a href="shift.php?id=' . $shift['ID'] . '">shift ' . $shift['ID'] . '</a>. This could cause problems in the report.');

                }
                $shift['shift_ID'] = $shift['ID'];
                $shift['activity_ID'] = $shift['activity'];
                $shift['activity'] = $this->activities->getName($shift['activity_ID']);
                $activityType = $this->activities->getType($shift['activity_ID']);
                if(!empty($activityType)) $shift['activity'] = $activityType . ' ' . $shift['activity'];
                $shift['minutes'] = !empty($time_difference) ? $time_difference : 0;
                $shift['cost'] = ($shift['minutes'] / 60) * $shift['rate'];
                $shift['weekday'] = date("l", strtotime($shift['date']));
                $total_recorded_minutes += $shift['minutes'];
                $total_employee_cost += ($shift['minutes'] / 60) * $this->get_worker($shift['worker'])['rate'];
            }
            $this->set_a_total($total_recorded_minutes);
            $this->set_a_total($total_employee_cost, 'employee_cost', 'money');

            return $this->shifts = $shifts;
        }
    }


    /**
     * @param string $amount
     * @param string $handle
     * @param string $type
     * @return mixed
     */
    function set_a_total($amount = '', $handle = 'total_recorded_time', $type = 'time')
    {
        switch ($type) {
            case 'money':
                $this->totals['formatted'][$handle] = ph_format_currency($amount);
                break;
            case 'percent':
                $this->totals['formatted'][$handle] = ph_format_percentage($amount);
                break;
            case 'time':
            default:
                $this->totals['formatted'][$handle] = ph_format_hours_minutes($amount);
                break;
        }
        $this->totals['amount'][$handle] = $amount;
        return true;
    }

    /**
     * @param string $sum_type
     * @return array|bool|mixed
     */
    function get_total($sum_type = '')
    {
        if (empty($sum_type)) {
            if (!empty($this->totals))
                return $this->totals;
            return false;
        }
        if (empty($this->totals[$sum_type]))
            return false;
        return $this->totals[$sum_type];
    }

}