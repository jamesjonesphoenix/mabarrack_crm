<?php

namespace Phoenix;

/**
 * @property Activity[] $activities
 *
 * Class Report
 */
class Report extends AbstractCRM
{
    /**
     * @var Activity[]
     */
    protected $_activities;

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
     * @param Activity[] $activities
     * @return Activity[]
     */
    protected function activities(array $activities = []): array
    {
        if ( !empty( $activities ) ) {
            $this->_activities = $activities;
        }
        return $this->_activities ?? [];
    }

    /**
     * @param $shifts
     * @return mixed
     */
    public function setupActivitySummary($shifts = array())
    {

        if ( empty( $shifts ) ) {
            $shifts = $this->getShifts();
            if ( empty( $shifts ) ) {
                return false;
            }
        }

        $activitiesSummary = array();

        foreach ( $shifts as $key => $shift ) {
            if ( empty( $activitiesSummary[$shift['activity']] ) ) {
                //$activity_id = $activities->getID( $shift[ 'activity' ] );
                $activitiesSummary[$shift['activity']] = array(
                    'activity_ID' => $shift['activity_ID'] ?? 'N/A',
                    'activity' => $shift['activity'],
                    'activity_hours' => $shift['minutes'],
                    'activity_cost' => $shift['cost'],
                );
            } else {
                $activitiesSummary[$shift['activity']]['activity_hours'] += $shift['minutes'];
                $activitiesSummary[$shift['activity']]['activity_cost'] += $shift['cost'];
            }
        }
        $format_columns = array(
            'activity_hours' => array('type' => 'hoursminutes'),
            'activity_cost' => array('type' => 'currency')
        );
        if ( !empty( $this->totals['amount']['total_recorded_time'] ) && $this->totals['amount']['total_recorded_time'] > 0 ) {
            foreach ( $activitiesSummary as &$activity ) {
                $activity['%_of_total_hours'] = $activity['activity_hours'] / $this->totals['amount']['total_recorded_time'];
            }
            unset( $activity );
            $format_columns['%_of_total_hours'] = array('type' => 'percentage');
        }
        if ( !empty( $this->totals['amount']['employee_cost'] ) && $this->totals['amount']['employee_cost'] > 0 ) {
            foreach ( $activitiesSummary as &$activity ) {
                $activity['%_of_total_employee_cost'] = $activity['activity_cost'] / $this->totals['amount']['employee_cost'];
            }
            unset( $activity );
            $format_columns['%_of_total_employee_cost'] = array('type' => 'percentage');
        }

        if ( !empty( $this->totals['amount']['time_paid'] ) ) {
            foreach ( $activitiesSummary as &$activity ) {
                $activity['%_of_hours_paid'] = $activity['activity_hours'] / $this->totals['amount']['time_paid'];
            }
            unset( $activity );
            $format_columns['%_of_hours_paid'] = array('type' => 'percentage');
        }

        return Format::tableValues( $activitiesSummary, $format_columns );
    }

    /**
     * @return mixed
     */
    public function getShifts()
    {
        if ( !empty( $this->shifts ) ) {
            return $this->shifts;
        }
        return $this->setupShifts();
    }

    /**
     * @param $workerID
     * @return bool|mixed
     */
    public function getWorker($workerID)
    {
        if ( empty( $workerID ) ) {
            return false;
        }
        if ( !empty( $this->workers[$workerID] ) ) {
            return $this->workers[$workerID];
        }
        $this->workers[$workerID] = $this->db->getRow(
            'users',
            array('ID' => $workerID),
            array('ID', 'name', 'rate')
        );
        if ( !empty( $this->workers[$workerID] ) ) {
            return $this->workers[$workerID];
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function setupShifts()
    {
        $shifts = $this->queryShifts();
        if ( empty( $shifts ) ) {
            return false;
        }

        $totalRecordedMinutes = 0;
        $totalEmployeeCost = 0;
        $warningString = '<strong>Not recorded</strong>';
        foreach ( $shifts as &$shift ) {
            $messageString = 'No %s was recorded for <a href="shift.php?id=' . $shift['ID'] . '">shift ' . $shift['ID'] . '</a>. This could cause problems in the report.';
            if ( !empty( $shift['time_started'] ) && !empty( $shift['time_finished'] ) ) {
                $timeDifference = DateTime::timeDifference( $shift['time_started'], $shift['time_finished'] );
            } else {
                if ( empty( $shift['time_started'] ) ) {
                    $messageString = sprintf( $messageString ,'start time');
                    $shift['time_started'] = $warningString;
                }
                if ( empty( $shift['time_finished'] ) ) {
                    $messageString = sprintf( $messageString ,'finish time');
                    $shift['time_started'] = $warningString;
                }
                $this->messages->add( $messageString );
            }


            $shift['shift_ID'] = $shift['ID'];
            $shift['activity_ID'] = $shift['activity'];

            $shift['activity'] = $this->activities[$shift['activity_ID']]->displayName;

            $shift['minutes'] = !empty( $timeDifference ) ? $timeDifference : 0;
            $shift['cost'] = ($shift['minutes'] / 60) * $shift['rate'];
            $shift['weekday'] = date( 'l', strtotime( $shift['date'] ) );
            $totalRecordedMinutes += $shift['minutes'];
            $totalEmployeeCost += ($shift['minutes'] / 60) * $this->getWorker( $shift['worker'] )['rate'];
        }
        unset( $shift );
        $this->setTotal( $totalRecordedMinutes );
        $this->setTotal( $totalEmployeeCost, 'employee_cost', 'money' );

        return $this->shifts = $shifts;
    }

    /**
     * @return array|bool
     */
    public function queryShifts()
    {
        return false;
    }

    /**
     * @param string $amount
     * @param string $handle
     * @param string $type
     * @return mixed
     */
    public function setTotal($amount = '', $handle = 'total_recorded_time', $type = 'time')
    {
        switch( $type ) {
            case 'money':
                $this->totals['formatted'][$handle] = Format::currency( $amount );
                break;
            case 'percent':
                $this->totals['formatted'][$handle] = Format::percentage( $amount );
                break;
            case 'time':
            default:
                $this->totals['formatted'][$handle] = Format::minutesToHoursMinutes( $amount );
                break;
        }
        $this->totals['amount'][$handle] = $amount;
        return true;
    }

    /**
     * @param string $sum_type
     * @return array|bool|mixed
     */
    public function getTotal($sum_type = '')
    {
        if ( empty( $sum_type ) ) {
            if ( !empty( $this->totals ) ) {
                return $this->totals;
            }
            return false;
        }
        if ( empty( $this->totals[$sum_type] ) ) {
            return false;
        }
        return $this->totals[$sum_type];
    }

}