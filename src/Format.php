<?php


namespace Phoenix;


use DateTime;
use Phoenix\Utility\DateTimeUtility;

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
                if ( $item === '-' || $item === 'N/A' ) {
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
        // DateTimeUtility::validateDate($date);

        $diffDate = date_create( $date );
        if ( empty( $diffDate ) ) {
            return $date;
        }

        $difference = (new DateTime())
            ->setTime( 0, 0, 0 )
            ->diff(
                $diffDate->setTime( 0, 0, 0 )
            );

        if ( $difference->d === 0 ) {
            return 'Today';
        }
        if ( $difference->d === 1 ) {
            if ( $difference->invert === 1 ) {
                return 'Yesterday';
            }
            return 'Tomorrow';
        }
        if ( empty( $allDays ) ) {
            return '';
        }
        $suffix = $difference->invert === 1 ? ' ago' : ' away';
        if ( $difference->y > 0 ) {
            if ( $difference->y === 1 ) {
                return 'Over a year' . $suffix;
            }
            return 'Over ' . $difference->y . ' years' . $suffix;
        }
        if ( $difference->m > 1 ) {
            return $difference->m . ' months' . $suffix;
        }
        return $difference->d . ' days' . $suffix;
    }

    /**
     * @param string $date
     * @return string
     */
    public
    static function annotateDateAllDays(string $date = ''): string
    {
        return self::annotateDate( $date, true );
    }

    /**
     * @param string $date
     * @param bool   $allDays
     * @return string
     */
    public
    static function annotateDate(string $date = '', $allDays = false): string
    {
        $dateTime = date_create( $date );
        if ( empty( $dateTime ) ) {
            return $date;
        }
        $annotation = self::daysFromTodayToWords( $date, $allDays );
        if ( !empty( $annotation ) ) {
            return $dateTime->format( 'd-m-Y' ) . ' <small class="d-print-none">(' . $annotation . ')</small>';
        }
        return $date;
    }


    public
    static function percentageExtraDecimals($value): string
    {

        return self::percentage($value, 3);
    }

    /**
     * @param float $value
     * @param int   $numberOfPlaces
     * @return string
     */
    public
    static function percentage($value, $numberOfPlaces = 1): string
    {
        if ( !is_numeric( $value ) ) {
            return $value;
        }
        // if ( $value === (float)0  ) { return '-'; }
        return number_format( 100 * $value, $numberOfPlaces ) . '%';
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
            case 'percentage':
            case 'percentageExtraDecimals':
                $methodName = $format;
                //'&#45'
                $shimArgs = array_merge( $shimArgs, [[
                    'match' => ',',
                    'shim' => '&puncsp;'
                ]] );
                break;
            case 'hoursminutes':
                $methodName = 'minutesToHoursMinutes';
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
            foreach ( $array as $key => $value ) {
                $formattedValue = self::$methodName( $value );
                if ( $formattedValue !== $value ) {
                    $newArray[$key] = $formattedValue;
                }
                //
            }
        }
        if ( empty( $doNotShim ) ) {
            $newArray = self::insertShims( $newArray ?? [], $shimArgs );
        }
        foreach ( $newArray ?? [] as $key => $value ) {
            $array[$key] = $value;
        }
        return $array;


        /*
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
        */
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
            if ( isset( $row[$column] ) ) {
                $array[$key] = $row[$column];
            }
        }
        $array = self::formatArrayValues( $array, $format );
        $outputColumn = !empty( $newColumn ) ? $newColumn : $column;
        foreach ( $array as $key => $item ) {
            $dataArray[$key][$outputColumn] = $item;
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