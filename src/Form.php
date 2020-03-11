<?php


namespace Phoenix;


class Form
{
    public $disabled;

    public function __construct($disabled = false)
    {
        $this->disabled = $disabled;
    }

    /**
     * @param array $options $options keys are <option> values, $options values are <option> names.
     * @param array $args 'selected', 'html' => [ 'id'=>'', 'name' => '', 'class' => '']
     * @return string
     */
    public function optionDropdown(array $options = [], array $args = []): string
    {
        $classes = $args['html']['class'] ?? '';
        $disabled = $args['disabled'] ?? $this->disabled ?? false;

        $html = '<select class="form-control viewinput ' . $classes . '"';
        //$html = '<select class="custom-select viewinput ' . $classes . '"';

        $html .= !empty( $args['html']['id'] ) ? ' id="' . $args['html']['id'] . '"' : '';
        $html .= !empty( $args['html']['name'] ) ? ' name="' . $args['html']['name'] . '"' : '';
        $html .= !empty( $disabled ) ? ' disabled=""' : '';
        $html .= ' autocomplete="off">';



        if ( !empty( $args['placeholder'] ) && !array_key_exists( '', $options ) ) {
            $options = ['' => $args['placeholder']] + $options;
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