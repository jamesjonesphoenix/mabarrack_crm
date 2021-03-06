<?php


namespace Phoenix\Report\Shifts\Worker;

use Phoenix\Utility\DateTimeUtility;

/**
 * Class TimeClockRecord
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class WorkerWeekTimeClockRecord extends WorkerWeekReport
{
    /**
     *
     */
    private const dayEndTime = '23:59:59';

    /**
     *
     */
    private const dayStartTime = '00:00:00';

    /**
     * @var int
     */
    private int $timeThisWeek;

    /**
     * @var int
     */
    private int $timeToday;

    /**
     * @var string
     */
    protected string $title = 'Time Clock Record';

    /**
     * @var array
     */
    protected array $columns = [
        'id' => 'ID',
        'day' => 'Day',
        'date' => [
            'title' => 'Date',
            'format' => 'annotateDate'
        ],
        'start_time' => 'Start Time',
        'finish_time' => 'Finish Time',
        'hours' => [
            'title' => 'Hours',
            'format' => 'hoursminutes'
        ],
        'total' => [
            'title' => 'Total Hours',
            'format' => 'hoursminutes'
        ],
        'lunch_start' => 'Lunch Start',
        'lunch_finish' => 'Lunch Finish',
    ];

    /**
     * @return array
     */
    protected function extractData(): array
    {
        if ( $this->shifts->getCount() === 0 ) {
            return [];
        }
        $timeClockRecord = $this->getEmptyData();
        if ( empty( $timeClockRecord ) ) {
            return [];
        }
        foreach ( $this->shifts->getAll() as $shift ) {
            $timeStartedSeconds = strtotime( $shift->timeStarted );
            $timeFinishedSeconds = strtotime( $shift->timeFinished );

            $date = date( 'Y-m-d', strtotime( $shift->date ) );
            if ( $timeStartedSeconds < $timeClockRecord[$date]['start_time_seconds'] ) { //if start time is earlier, use this shift's start time
                $timeClockRecord[$date]['start_time'] = date( 'H:i:s', $timeStartedSeconds );
                $timeClockRecord[$date]['start_time_seconds'] = $timeStartedSeconds;
            }
            if ( $timeFinishedSeconds > $timeClockRecord[$date]['finish_time_seconds'] ) { //if finish time is later, us this shift's finish time
                $timeClockRecord[$date]['finish_time'] = date( 'H:i:s', $timeFinishedSeconds );
                $timeClockRecord[$date]['finish_time_seconds'] = $timeStartedSeconds;
            }

            if ( $shift->activity->name === 'Lunch' ) {
                $timeClockRecord[$date]['lunch_start'] = date( 'H:i', $timeStartedSeconds );
                $timeClockRecord[$date]['lunch_finish'] = date( 'H:i', $timeFinishedSeconds );
                $timeClockRecord[$date]['lunch_minutes'] += $shift->getShiftLength();
            } else {
                $timeClockRecord[$date]['hours'] += $shift->getShiftLength();
                $timeClockRecord[$date]['minutes'] += $shift->getShiftLength();
            }
        }

        $totalPayMinutes = 0; //total minutes to be paid
        foreach ( $timeClockRecord as &$day ) {
            $totalPayMinutes += $day['minutes'];
            $day['total'] = $totalPayMinutes;
        }
        unset( $day );

        $this->timeThisWeek = $totalPayMinutes;
        $today = date( 'Y-m-d' );
        if ( !empty( $timeClockRecord[$today] ) ) {
            $this->timeToday = $timeClockRecord[$today]['minutes'];
        }

        return $timeClockRecord;
    }

    /**
     * @return string
     */
    public function getTotalHoursToday(): string
    {
        return $this->format::minutesToHoursMinutes(
            $this->timeToday ?? 0
        );
    }

    /**
     * @return string
     */
    public function getTotalHoursThisWeek(): string
    {
        return $this->format::minutesToHoursMinutes(
            $this->timeThisWeek ?? 0
        );
    }

    /**
     * @return array
     */
    private function getEmptyData(): array
    {
        $dayList = ['Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
        //$dateo->format( 'd-m-Y' );

        //Reorder days so we have correct start day
        $dateStart = $this->getDateStart();
        if ( empty( $dateStart ) ) {
            return [];
        }
        $dayStart = date( 'l', strtotime( $dateStart ) );
        $dayStartIndex = array_search( $dayStart, $dayList, true );
        for ( $i = 0; $i < $dayStartIndex; $i++ ) {
            $dayList[] = array_shift( $dayList );
        }
        foreach ( $dayList as $key => $day ) { //initialise array for each day
            $date = date( 'Y-m-d', strtotime( $dateStart . '+ ' . $key . ' day' ) );
            $timeClockRecord[$date] = [
                'id' => $key,
                'day' => $day,
                'date' => $date,
                'start_time' => self::dayEndTime,
                'finish_time' => self::dayStartTime,
                'minutes' => 0,
                'hours' => 0,
                'lunch_start' => ' ',
                'lunch_finish' => ' ',
                'lunch_minutes' => 0
            ];

            $timeClockRecord[$date]['start_time_seconds'] = strtotime( $timeClockRecord[$date]['start_time'] );
            $timeClockRecord[$date]['finish_time_seconds'] = strtotime( $timeClockRecord[$date]['finish_time'] );
        }
        return $timeClockRecord ?? [];
    }

    /**
     * @param array $data
     * @return array
     */
    public function applyDefaults(array $data = []): array
    {
        foreach ( $data as $date => &$day ) {
            if ( $day['start_time'] === self::dayEndTime ) {
                if ( in_array( $day['day'], ['Saturday', 'Sunday'], true ) ) {
                    $day['start_time'] = '-';
                } elseif ( DateTimeUtility::isAfter( $day['date'], date( 'd-m-Y' ), false ) ) {
                    $day['start_time'] = 'N/A';
                } else {
                    $day['start_time'] = 'On Leave';
                }
            }
            if ( $day['finish_time'] === self::dayStartTime ) {
                $day['finish_time'] = '-';
            }
            if ( $day['lunch_minutes'] === 0 ) {
                $day['lunch_start'] = '-';
                $day['lunch_finish'] = '-';
            }
        }
        return parent::applyDefaults( $data );
    }


}