<?php


namespace Phoenix;


class Format
{
    /**
     * Convert minutes to hours:minutes
     *
     * @param int $minutes
     * @param int $orderOfMagnitude
     * @return string
     */
    public static function minutesToHoursMinutes(int $minutes = 0, int $orderOfMagnitude = 0): string
    {
        if ( $minutes === 0 ) {
            return self::insertShim( 0, $orderOfMagnitude ) . '-';
        }
        $hours = floor( $minutes / 60 );
        $hoursAndMinutes = self::insertShim( $hours, $orderOfMagnitude );
        $hoursAndMinutes .= $hours . ':';
        $hoursAndMinutes .= str_pad( ($minutes % 60), 2, '0', STR_PAD_LEFT );
        return $hoursAndMinutes;
    }


    /**
     * Inserts spaces so that different length strings will end up equal length
     *
     * @param float $value
     * @param int $orderOfMagnitude
     * @return string
     */
    public static function insertShim(float $value, int $orderOfMagnitude = 0): string
    {
        if ( $orderOfMagnitude === 0 ) {
            return '';
        }
        if ( $value < 0 ) {
            return '';
        }
        $numberOfShims = $orderOfMagnitude - max( 0, floor( log10( $value ) ) );
        return str_repeat( '&#x2007;', $numberOfShims ) ?? '';
    }

    /**
     * @param int|string $value
     * @param int $orderOfMagnitude
     * @return string
     */
    public
    static function currency($value = 0, int $orderOfMagnitude = 0): string
    {
        if ( !is_numeric( $value ) ) {
            return $value;
        }
        return self::insertShim( $value, $orderOfMagnitude ) . '$' . number_format( $value, 2 );
    }

    /**
     * ph_format_percentage
     *
     * @param float $value
     * @param bool $forTable
     * @return string
     */
    public
    static function percentage(float $value, bool $forTable = false): string
    {
        if ( $value === 0 ) {
            return '-';
        }
        $formattedValue = number_format( 100 * $value, 1 ) . '%';
        if ( $forTable && $value < 0.1 ) {
            $formattedValue = '&#x2007;' . $formattedValue; //insert shim
        }
        return $formattedValue;
    }

    /**
     * @param $tableArray
     * @param $columns
     * @return mixed
     */
    public
    static function tableValues($tableArray, $columns)
    {
        foreach ( $columns as $column => $columnData ) {
            $max_value = 0;
            $outputKey = !empty( $columnData['output_column'] ) ? $columnData['output_column'] : $column;
            foreach ( $tableArray as $key => $array ) {
                if ( !is_numeric( $tableArray[$key][$column] ) ) {
                    ph_messages()->add( 'Non numeric value "<strong>' . $tableArray[$key][$column] . '</strong>" in column "<strong>' . $column . '</strong>" sent to format function. You probably already formatted the value mistakenly' );
                    return $tableArray;
                    break;
                }
                $max_value = max( $max_value, $tableArray[$key][$column] );
            }
            switch( $columnData['type'] ) {
                case 'currency' :
                    $maxOrderOfMagnitude = max( 0, floor( log10( $max_value ) ) );
                    foreach ( $tableArray as $key => &$row ) {
                        $row[$outputKey] = self::currency( $row[$column], $maxOrderOfMagnitude );
                    }
                    unset( $row );
                    break;
                case 'hoursminutes':
                    $maxOrderOfMagnitude = max( 0, floor( log10( $max_value / 60 ) ) );
                    foreach ( $tableArray as $key => &$row ) {
                        $row[$outputKey] = self::minutesToHoursMinutes( $row[$column], $maxOrderOfMagnitude );
                    }
                    unset( $row );
                    break;
                case 'percentage':
                    $forTable = $max_value >= 0.1;
                    foreach ( $tableArray as $key => &$row ) {
                        $row[$outputKey] = self::percentage( $row[$column], $forTable );
                    }
            }
        }
        return $tableArray;
    }

    /**
     * @param array $options $options keys are <option> values, $options values are <option> names.
     * @param array $args 'selected', 'html' => [ 'id'=>'', 'name' => '', 'class' => '']
     * @return string
     */
    public
    static function optionDropdown(array $options = [], array $args = []): string
    {
        $classes = $args['html']['class'] ?? '';

        $html = '<select class="form-control viewinput ' . $classes . '"';
        //$html = '<select class="custom-select viewinput ' . $classes . '"';

        $html .= !empty( $args['html']['id'] ) ? ' id="' . $args['html']['id'] . '"' : '';
        $html .= !empty( $args['html']['name'] ) ? ' name="' . $args['html']['name'] . '"' : '';
        $html .= ' autocomplete="off">';

        if ( !empty( $args['placeholder'] )) {
            if ( !array_key_exists( '', $options ) ) {
                $options = ['' => $args['placeholder']] + $options;
            }
        }

        foreach ( $options as $value => $option ) {
            $selected = '';
            if (
                (!empty( $args['selected'] ) || $args['selected'] === 0)
                && $args['selected'] === $value
            ) {
                $selected = ' selected="selected"';
            }
            $html .= '<option value="' . $value . '" ' . $selected . '>' . ucwords( str_replace( '_', ' ', $option ) ) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
}