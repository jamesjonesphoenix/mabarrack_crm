<?php

class ph_DateTime
{

    public static $dud_times = array( '0000-00-00 00:00:00', '0000-00-00', '01-01-1970', '1970-01-01', '00-00-0000' );

    /*
  * validate date of form Y-m-d or d-m-Y is a real date.
  */
    public static function validate_date( $date, $dmy = false ) {

        if ( empty( $date ) || in_array( $date, self::$dud_times ) )
            return false;
        $date_array = explode( '-', $date );
        if ( empty( $date_array[ 0 ] ) || empty( $date_array[ 1 ] ) || empty( $date_array[ 2 ] ) )
            return false;
        if ( $dmy ) {
            if ( checkdate( $date_array[ 1 ], $date_array[ 0 ], $date_array[ 2 ] ) )
                return $date;
        } else {
            if ( checkdate( $date_array[ 1 ], $date_array[ 2 ], $date_array[ 0 ] ) )
                return $date;
        }
        return false;
        /*

        $datetime = strtotime( $date );
        if ( $datetime <= 0 )
            return false;
        $date = date( "Y-m-d", $datetime );
        $date_array = explode( '-', $date );
        if ( checkdate( $date_array[ 1 ], $date_array[ 2 ], $date_array[ 0 ] ) )
            return $date;
        else
            return false;
        */


    }

    public static function time_difference( $time_start, $time_finish ) {
        if ( empty( $time_start ) || empty( $time_finish ) )
            return false;
        $time_start = strtotime( $time_start );
        $time_finish = strtotime( $time_finish );
        if ( $time_start <= 0 || $time_finish <= 0 )
            return false;
        $minutes = ( $time_finish - $time_start ) / 60;
        return $minutes;

    }
}