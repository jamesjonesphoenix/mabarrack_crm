<?php


namespace Phoenix;


use DateTime;

/**
 * Class Format
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class Format
{
    /**
     * @param array $array
     * @param array $args
     * @return array
     */
    public static function insertShims(array $array = [], array $args = []): array
    {
        if ( empty( $array ) ) {
            return [];
        }
        foreach ( $args as &$arg ) {
            foreach ( $array as $key => $item ) {
                if ( !empty( $arg['regular_expression'] ) ) {
                    $arg['count'][$key] = preg_match_all( $arg['match'], $item );
                } else {
                    $arg['count'][$key] = substr_count( $item, $arg['match'] );
                }
            }
        }
        unset( $arg );
        foreach ( $args as &$arg ) {
            foreach ( $array as $key => &$item ) {
                if($item === '-' || $item === 'N/A'){
                    continue;
                }
                $shimsToAdd = max( $arg['count'] ) - $arg['count'][$key];
                $item = str_repeat( $arg['shim'], $shimsToAdd ) . $item;
            }
        }
        return $array;
    }

    /**
     * Convert minutes to hours:minutes
     *
     * @param int $minutes
     * @return string
     */
    public static function minutesToHoursMinutes($minutes = 0): string
    {
        if ( !is_int( $minutes ) ) {
            return $minutes;
        }
        if ( $minutes === 0 ) {
            return '0:00';
            //return '-';
        }
        $sign = '';
        if ( $minutes < 0 ) {
            $sign = '-';
            $minutes *= -1;
        }
        $hours = floor( $minutes / 60 );
        $hoursAndMinutes = $sign . $hours . ':';
        $hoursAndMinutes .= str_pad( ($minutes % 60), 2, '0', STR_PAD_LEFT );
        return $hoursAndMinutes;
    }

    /**
     * @param int|string $value
     * @return string
     */
    public
    static function currency($value = 0): ?string
    {
        if ( !is_numeric( $value ) ) {
            return $value;
        }
        $negativeSign = '';
        if ( $value < 0 ) {
            $value = -1 * $value;
            $negativeSign = '-';
            //$negativeSign = '&#45';
            //$negativeSign = '&minus;'; // &#45;
        }
        return $negativeSign . '$' . number_format( $value, 2 );
    }

    /**
     * @param string $value
     * @return string
     */
    public
    static function date(string $value = ''): string
    {
        if ( empty( $value ) ) {
            return '';
        }
        if ( !DateTimeUtility::validateDate( $value ) ) {
            return $value;
        }
        return date( 'd-m-Y', strtotime( $value ) );
    }

    /**
     * @param string $date
     * @param false  $allDays
     * @return string
     */
    public
    static function daysFromTodayToWords(string $date = '', $allDays = false): string
    {
        if ( empty( $date ) ) {
            return '';
        }
        $today = new DateTime(); // This object represents current date/time
        $today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison
        //d($date);
        $matchDate = DateTime::createFromFormat( 'd-m-Y', $date );
        $matchDate->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

        $difference = $today->diff( $matchDate );
        $differenceDays = (integer)$difference->format( '%R%a' );

        switch( $differenceDays ) {
            case 0:
                return 'Today';
            case -1:
                return 'Yesterday';
            case 1:
                return 'Tomorrow';
        }
        if ( empty( $allDays ) ) {
            return '';
        }
        $differenceMonths = (integer)$difference->format( '%m' );
        $differenceYears = (integer)$difference->format( '%y' );
        if ( $differenceDays < -1 ) {
            $daysToCalcWith = -1 * $differenceDays;
            $suffix = ' ago';
            //return $differenceDays * -1 . ' days ago';
        } else {
            $suffix = ' away';
            $daysToCalcWith = $differenceDays;
            //return $differenceDays . ' days away';
        }
        if ( $differenceYears > 0 ) {
            if ( $differenceYears === 1 ) {
                return 'Over a year' . $suffix;
            }
            return 'Over ' . $differenceYears . ' years' . $suffix;
        }
        if ( $differenceMonths > 1 ) {
            return $differenceMonths . ' months' . $suffix;
        }
        return $daysToCalcWith . ' days' . $suffix;
    }

    /**
     * @param string $date
     * @return string
     */
    public
    static function annotateDateAllDays(string $date = ''): string
    {
        return self::annotateDate($date, true);
    }

    /**
     * @param string $date
     * @param bool   $allDays
     * @return string
     */
    public
    static function annotateDate(string $date = '', $allDays = false): string
    {
        $date = self::date($date);
        if ( empty( $date ) ) {
            return $date;
        }
        /*
        if ( strlen( $date ) > 10 ) {
            return $date;
        }
        */
        $annotation = self::daysFromTodayToWords( $date, $allDays );
        if ( !empty( $annotation ) ) {
            $date .= ' <small class="d-print-none">(' . $annotation . ')</small>';
        }
        return $date;
    }

    /**
     * @param float $value
     * @return string
     */
    public
    static function percentage($value): string
    {
        if ( !is_numeric( $value ) ) {
            return $value;
        }
        // if ( $value === (float)0  ) { return '-'; }
        return number_format( 100 * $value, 1 ) . '%';
    }

    /**
     * @param array  $array
     * @param string $format
     * @return array
     */
    public
    static function formatArrayValues(array $array = [], string $format = ''): array
    {
        if ( empty( $array ) ) {
            return [];
        }
        $shimArgs = [[
            'match' => '/[0-9]/',
            'shim' => '&#x2007;',
            'regular_expression' => true
        ], [
            'match' => '&minus;',
            'shim' => '&#x2007;',
        ], [
            'match' => '-',
            'shim' => '&#8196;',
        ]];
        switch( $format ) {
            case 'currency' :
                $methodName = 'currency';
                //'&#45'
                $shimArgs = array_merge( $shimArgs, [[
                    'match' => ',',
                    'shim' => '&puncsp;'
                ]] );
                break;
            case 'hoursminutes':
                $methodName = 'minutesToHoursMinutes';
                break;
            case 'percentage':
                $methodName = 'percentage';
                break;
            case 'date':
                $methodName = 'date';
                $doNotShim = true;
                break;
            case 'annotateDate':
                $methodName = 'annotateDate';
                $doNotShim = true;
                break;
            case 'annotateDateAllDays':
                $methodName = 'annotateDateAllDays';
                $doNotShim = true;
                break;
            case 'daysFromTodayToWords':
                $methodName = 'daysFromTodayToWords';
                $doNotShim = true;
                break;
            case 'number':
                return self::insertShims( $array, $shimArgs );
            default:
                return $array;
        }
        if ( !empty( $methodName ) ) {
            foreach ( $array as &$value ) {
                $value = self::$methodName( $value );
            }
        }
        unset( $value );
        if ( empty( $doNotShim ) ) {
            $array = self::insertShims( $array, $shimArgs );
        }
        return $array;
    }

    /**
     * @param array  $dataArray
     * @param string $format
     * @param string $column
     * @param string $newColumn
     * @return array
     */
    public static function formatColumnValues(array $dataArray = [], string $format = '', string $column = '', $newColumn = ''): array
    {
        $array = [];
        foreach ( $dataArray as $key => $row ) {
            $array[$key] = $row[$column];
        }
        $array = self::formatArrayValues( $array, $format );
        $outputColumn = !empty( $newColumn ) ? $newColumn : $column;
        foreach ( $dataArray as $key => &$row ) {
            $row[$outputColumn] = $array[$key];
        }
        return $dataArray;
    }

    /**
     * @param array $dataArray
     * @param array $columnFormats array with column name keys and format values
     * @return array
     */
    public static function formatColumnsValues(array $dataArray, array $columnFormats = []): array
    {
        foreach ( $columnFormats as $columnName => $format ) {
            $dataArray = self::formatColumnValues( $dataArray, $format, $columnName );
        }
        return $dataArray;
    }

}