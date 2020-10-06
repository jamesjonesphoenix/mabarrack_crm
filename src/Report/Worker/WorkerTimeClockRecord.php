<?php


namespace Phoenix\Report\Worker;

use Phoenix\DateTimeUtility;

/**
 * Class TimeClockRecord
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class WorkerTimeClockRecord extends WorkerReport
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
        'date' =>  ['title' => 'Date', 'format' => 'annotateDate'],
        'start_time' => 'Start Time',
        'finish_time' => 'Finish Time',
        'hours' =>  ['title' => 'Hours', 'format' => 'hoursminutes'],
        'total' =>  ['title' => 'Total Hours', 'format' => 'hoursminutes'],
        'lunch_start' => 'Lunch Start',
        'lunch_finish' => 'Lunch Finish',
    ];

    /**
     * @return array
     */
    public function extractData(): array
    {
        $timeClockRecord = $this->getEmptyData();

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

            if ( $shift->activity === 0 || $shift->activity->name === 'Lunch' ) {
                $timeClockRecord[$date]['lunch_start'] = date( 'H:i', $timeStartedSeconds );
                $timeClockRecord[$date]['lunch_finish'] = date( 'H:i', $timeFinishedSeconds );
                $timeClockRecord[$date]['lunch_minutes'] += $shift->getShiftLength();
            } else {
                $timeClockRecord[$date]['hours'] += $shift->getShiftLength();
                $timeClockRecord[$date]['minutes'] += $timeClockRecord[$date]['hours'];
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
        $minutes = $this->timeToday ?? 0;
        return $this->format::minutesToHoursMinutes( $minutes );
    }

    /**
     * @return string
     */
    public function getTotalHoursThisWeek(): string
    {
        $minutes = $this->timeThisWeek ?? 0;
        return $this->format::minutesToHoursMinutes( $minutes );
    }

    /**
     * @return array
     */
    private function getEmptyData(): array
    {
        $dayList = ['Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
        //$dateo->format( 'd-m-Y' );

        //Reorder days so we have correct start day
        $dayStart = date( 'l', strtotime( $this->getDateStart() ) );
        $dayStartIndex = array_search( $dayStart, $dayList, true );
        for ( $i = 0; $i < $dayStartIndex; $i++ ) {
            $dayList[] = array_shift( $dayList );
        }
        foreach ( $dayList as $key => $day ) { //initialise array for each day
            $date = date( 'Y-m-d', strtotime( $this->getDateStart() . '+ ' . $key . ' day' ) );
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
     * @return string
     * @throws \Exception
     */
    public function renderReport(): string
    {
        $html = '';
        if ( $this->shifts->getCount() === 0 ) {
            $html .= $this->htmlUtility::getAlertHTML( 'No completed shifts found from <strong>' . $this->getDateStart() . '</strong> to <strong>' . $this->getDateFinish() . '</strong> to report.', 'warning' );
        }
        $data = $this->extractData();
        if ( empty( $data ) ) {
            return $this->htmlUtility::getAlertHTML( 'Shifts found from <strong>' . $this->getDateStart() . '</strong> to <strong>' . $this->getDateFinish() . '</strong> but no report data generated. Something has gone wrong.', 'danger' );
        }


        foreach ( $data as $date => &$day ) {
            if ( $day['start_time'] === self::dayEndTime ) {
                if ( in_array( $day['day'], ['Saturday', 'Sunday'], true ) ) {
                    $day['start_time'] = '-';
                } elseif ( DateTimeUtility::timeDifference( date( 'd-m-Y' ), $day['date'] ) > 0 ) {
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
        unset( $day );

        $data = $this->format::formatColumnsValues( $data, $this->getColumns('format') );
        return $html . $this->htmlUtility::getTableHTML( [
                'data' => $data,
                'columns' => $this->getColumns()
            ] );
    }



    /**
     * @return array
     */
    public function getNavLinks(): array
    {
        $strStart = 'start_date=';
        $url = $_SERVER['REQUEST_URI'];

        if ( empty( parse_url( $url, PHP_URL_QUERY ) ) ) {
            $url .= '?';
        } else {
            $url = str_replace(
                $strStart . $this->getDateStart(),
                '',
                $url );
            $url = trim( $url, '&' );
            if ( substr( $url, -1 ) !== '?' ) {
                $url .= '&';
            }

        }
        $url .= $strStart;
        return [
            [
                'url' => $url . $this->getDatePrevious(),
                'text' => 'Previous Week'
            ], [
                'url' => $url . $this->getDateNext(),
                'text' => 'Next Week'
            ], [
                'url' => '#',
                'text' => 'Print'
            ]
        ];
    }
}