<?php

namespace Phoenix\Utility;

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
     * @param string $format
     * @return float|int
     */
    public static function timeDifference(string $timeStart = '', string $timeFinish = '', string $format = 'minutes'): int
    {
        $difference = date_create( $timeStart )->diff( date_create( $timeFinish ) );

        $operator = $difference->invert === 1 ? -1 : 1;
        switch( $format ) {
            case 'minutes':
                $sum = ($difference->days * 1440 /* 24 * 60 */) + ($difference->h * 60) + $difference->i;
                break;
            case 'days':
                $sum = $difference->days;
                break;
            case 'seconds':
            default:
                $sum = ($difference->days * 86400 /* 24 * 60 * 60 */) + ($difference->h * 3600) + ($difference->i * 60) + $difference->s;
        }
        return $operator * $sum;
    }

    /**
     * @param string $timeStamp
     * @param string $timeStampToCheckAgainst
     * @param bool   $trueIfEquals
     * @return bool
     */
    public static function isBefore(string $timeStamp = '', string $timeStampToCheckAgainst = '', bool $trueIfEquals = true): bool
    {
        $timeDifference = self::timeDifference( $timeStamp, $timeStampToCheckAgainst, 'seconds' );
        if ( $trueIfEquals ) {
            return $timeDifference >= 0;
        }
        return $timeDifference > 0;
    }

    /**
     * @param string $timeStamp
     * @param string $timeStampToCheckAgainst
     * @param bool   $trueIfEquals
     * @return bool
     */
    public static function isAfter(string $timeStamp = '', string $timeStampToCheckAgainst = '', bool $trueIfEquals = true): bool
    {
        $timeDifference = self::timeDifference( $timeStamp, $timeStampToCheckAgainst, 'seconds' );
        if ( $trueIfEquals ) {
            return $timeDifference <= 0;
        }
        return $timeDifference < 0;
    }

    /**
     * Rounds time to the nearest minute
     *
     * @param string $timestamp
     * @param int    $upDown
     * @return false|string
     */
    public static function roundTime(string $timestamp = '', int $upDown = 0)
    {
        if ( empty( $timestamp ) ) {
            $timestamp = date( 'H:i:s' );
        }
        $precision = 60; // 1 minute, was 360 for rounding to nearest 6 minutes
        $unixTime = strtotime( $timestamp ) / $precision;
        if ( $upDown === 1 ) {
            $unixTime = ceil( $unixTime );
        } elseif ( $upDown === -1 ) {
            $unixTime = floor( $unixTime );
        } else {
            $unixTime = round( $unixTime );
        }
        return date( 'H:i', $unixTime * $precision );
    }

}