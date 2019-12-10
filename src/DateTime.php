<?php

namespace Phoenix;

/**
 * Class DateTime
 * 
 * @package Phoenix
 */
class DateTime
{

    /**
     * @var array
     */
    public static $dudTimes = array( '0000-00-00 00:00:00', '0000-00-00', '01-01-1970', '1970-01-01', '00-00-0000' );

    /**
     * validate date of form Y-m-d or d-m-Y is a real date.
     *
     * @param string $date
     * @param bool $dmy
     * @return string|bool
     */
    public static function validateDate($date = '', $dmy = false ) {

        if ( empty( $date ) || in_array( $date, self::$dudTimes, false) ) {
            return false;
        }
        $dateArray = explode( '-', $date );
        if ( empty( $dateArray[ 0 ] ) || empty( $dateArray[ 1 ] ) || empty( $dateArray[ 2 ] ) ) {
            return false;
        }
        if ( $dmy ) {
            if ( checkdate( $dateArray[ 1 ], $dateArray[ 0 ], $dateArray[ 2 ] ) ) {
                return $date;
            }
        } else if ( checkdate( $dateArray[ 1 ], $dateArray[ 2 ], $dateArray[ 0 ] ) ) {
            return $date;
        }
        return false;
        /*
        $datetime = strtotime( $date );
        if ( $datetime <= 0 )
            return false;
        $date = date( "Y-m-d", $datetime );
        $dateArray = explode( '-', $date );
        if ( checkdate( $dateArray[ 1 ], $dateArray[ 2 ], $dateArray[ 0 ] ) )
            return $date;
        else
            return false;
        */
    }

    /**
     * Returns time difference between two times in minutes
     * 
     * @param string $timeStart
     * @param string $timeFinish
     * @return float|int
     */
    public static function timeDifference(string $timeStart = '', string $timeFinish = '') {
        if ( empty( $timeStart ) || empty( $timeFinish ) ) {
            return 0;
        }
        $timeStart = strtotime( $timeStart );
        $timeFinish = strtotime( $timeFinish );
        if ( empty($timeStart) || empty($timeFinish) || $timeStart <= 0 || $timeFinish <= 0 ) {
            return 0;
        }
        return ( $timeFinish - $timeStart ) / 60;
    }

    /**
     * @param $timestamp
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