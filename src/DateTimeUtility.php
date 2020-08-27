<?php

namespace Phoenix;

/**
 * Class DateTime
 *
 * @package Phoenix
 */
class DateTimeUtility
{

    /**
     * @var array
     */
    public static array $dudTimes = ['0000-00-00 00:00:00', '0000-00-00', '01-01-1970', '1970-01-01', '00-00-0000'];

    /**
     * validate date of form Y-m-d or d-m-Y is a real date.
     *
     * @param string $date
     * @param bool   $dmy If set to true validate d-m-Y. If set to false validate Y-m-d
     * @return bool
     */
    public static function validateDate($date = '', $dmy = false): bool
    {
        if ( empty( $date ) || in_array( $date, self::$dudTimes, false ) ) {
            return false;
        }
        $dateArray = explode( '-', $date );
        if ( empty( $dateArray[0] ) || empty( $dateArray[1] ) || empty( $dateArray[2] ) ) {
            return false;
        }
        if ( $dmy ) {
            return checkdate( $dateArray[1], $dateArray[0], $dateArray[2] );
        }
        return checkdate( $dateArray[1], $dateArray[2], $dateArray[0] );
    }

    /**
     * Returns time difference between two times in minutes
     *
     * @param string $timeStart
     * @param string $timeFinish
     * @return float|int
     */
    public static function timeDifference(string $timeStart = '', string $timeFinish = '')
    {
        if ( empty( $timeStart ) || empty( $timeFinish ) ) {
            return 0;
        }
        $timeStartTime = strtotime( $timeStart );
        $timeFinishTime = strtotime( $timeFinish );
        if ( empty( $timeStartTime ) || empty( $timeFinishTime ) || $timeStartTime <= 0 || $timeFinishTime <= 0 ) {
            return 0;
        }
        return ($timeFinishTime - $timeStartTime) / 60;
    }

    /**
     * Rounds time to the nearest minute
     *
     * @param     $timestamp
     * @param int $upDown
     * @return false|string
     */
    public static function roundTime($timestamp, int $upDown = 0)
    {
        $precision = 6;
        $timestamp = strtotime( $timestamp );
        $precision = 60 * $precision;
        if ( $upDown === 1 ) {
            $timestamp = ceil( $timestamp / $precision ) * $precision;
        } elseif ( $upDown === -1 ) {
            $timestamp = floor( $timestamp / $precision ) * $precision;
        } else {
            $timestamp = round( $timestamp / $precision ) * $precision;
        }
        return date( 'H:i:s', $timestamp );
    }

}