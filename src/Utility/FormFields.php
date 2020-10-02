<?php


namespace Phoenix\Utility;

use Phoenix\Format;

/**
 * Class FormFields
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class FormFields extends HTMLTags
{
    /**
     * @param array $args
     * @return string
     */
    public static function getCheckboxesFieldHTML(array $args = []): string
    {
        $args = self::mergeDefaultArgs( $args, 'hidden' );
        $args['class'] .= ' form-control';
        ob_start();
        ?>
        <input autocomplete="off" type="hidden"<?php echo self::getAttributes( $args ); ?>>
        <?php
        return ob_get_clean();
    }


    /**
     * @param array $args
     * @return string
     */
    public static function getOptionDropdownFieldHTML(array $args = []): string
    {
        $args = self::mergeDefaultArgs( $args, 'options_dropdown' );
        $args['class'] .= ' custom-select';
        $args['class'] .= ' form-control';
        $options = $args['options'] ?? [];
        if ( !empty( $args['placeholder'] ) && !array_key_exists( '', $options ) ) {
            $options = ['' => $args['placeholder']] + $options;
        }
        $selectedValue = $args['selected'] ?? '';

        ob_start();
        echo self::getFieldLabelHTML(
            $args['label'] ?? '',
            $args['id']
        );
        ?>
        <div class="row no-gutters">
            <div class="col">
                <div class="input-group">
                    <select autocomplete="off"<?php echo self::getAttributes( $args ); ?>>
                        <?php foreach ( $options as $optionValue => $optionString ) {
                            $selected = $selectedValue === $optionValue ? self::makeElementProperty( 'selected', 'selected' ) : '';
                            $optionValueProperty = self::makeElementProperty( $optionValue, 'value' ); ?>
                            <option<?php echo $optionValueProperty . $selected; ?>><?php echo str_replace( '_', ' ', $optionString ) ?></option><?php
                        } ?>
                    </select>
                    <?php if ( !empty( $args['append'] ) ) { ?>
                        <div class="input-group-append">
                            <?php echo $args['append'] ?? ''; ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getHiddenFieldHTML(array $args = []): string
    {
        $args = self::mergeDefaultArgs( $args, 'hidden' );
        $args['class'] .= ' form-control';
        ob_start(); ?>
        <input autocomplete="off" type="hidden"<?php echo self::getAttributes( $args ); ?>>
        <?php return ob_get_clean();
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getTextFieldHTML(array $args = []): string
    {
        $args = self::mergeDefaultArgs( $args, 'hidden' );
        $args['class'] .= ' form-control';
        //$type = 'text';
        ob_start();
        //$type
        echo self::getFieldLabelHTML(
            $args['label'] ?? '',
            $args['id']
        );
        ?>
        <input autocomplete="off" type="text"<?php echo self::getAttributes( $args ); ?>>
        <?php
        if ( !empty( $args['small'] ) ) {
            ?><small><?php echo $args['small']; ?></small><?php
        }
        return ob_get_clean();
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getIntegerFieldHTML(array $args = []): string
    {
        return self::getFormFieldHTML( 'number', $args );
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getCurrencyFieldHTML(array $args = []): string
    {
        $args['prepend'] = '$';
        return self::getFormFieldHTML( 'currency', $args );
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getDateFieldHTML(array $args = []): string
    {
        return self::getFormFieldHTML( 'date', $args );
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getTimeFieldHTML(array $args = []): string
    {
        return self::getFormFieldHTML( 'time', $args );
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getTextAreaFieldHTML(array $args = []): string
    {
        return self::getFormFieldHTML( 'textarea', $args );
    }


    /**
     * @param array $args
     * @return string
     */
    public static function getEmailFieldHTML(array $args = []): string
    {
        return self::getFormFieldHTML( 'email', $args );
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getPasswordFieldHTML(array $args = []): string
    {
        $args = self::mergeDefaultArgs( $args, 'password' );
        $args['class'] .= ' form-control';

        $suffix = '-2';

        ob_start();
        echo self::getFieldLabelHTML(
            $args['label'] ?? '',
            $args['id']
        );

        ?>
        <div class="input-group"><?php
        if ( !empty( $args['change_password_toggle'] ) ) { ?>
            <div class="input-group-prepend">
                <?php echo self::getButton( [
                    'type' => 'button',
                    'id' => 'change-password-button',
                    'class' => 'btn btn-info',
                    'content' => 'Change Password'
                ] ) ?>
            </div>
        <?php }
        echo self::getTextFieldHTML( [
            'id' => $args['id'],
            'name' => $args['name'],
            'placeholder' => 'Enter Password',
            'disabled' => true
        ] );
        echo self::getTextFieldHTML( [
            'id' => $args['id'] . $suffix,
            'name' => $args['name'] . $suffix,
            'placeholder' => 'Confirm Password',
            'disabled' => true
        ] );
        ?></div><?php
        return ob_get_clean();
        //return self::getFormFieldHTML( 'password', $args );
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getFileFieldHTML(array $args = []): string
    {
        return self::getFormFieldHTML( 'file', $args );
    }

    /**
     * @param string $text label text
     * @param string $for
     * @return string
     */
    public static function getFieldLabelHTML(string $text = '', string $for = ''): string
    {
        if ( empty( $text ) ) {
            return '';
        }

        $text = rtrim( $text, ':' ) . ':';

        $for = !empty( $for ) ? ' for="' . $for . '"' : '';
        return '<label' . $for . '>' . $text . '</label>';
    }

    /**
     * @param string $type
     * @param array  $args
     * @return string
     */
    private static function getFormFieldHTML(string $type = '', array $args = []): string
    {
        $args = self::mergeDefaultArgs( $args, $type );
        $args['class'] .= ' form-control';
        ob_start();
        echo self::getFieldLabelHTML(
            $args['label'] ?? '',
            $args['id']
        );
        if ( !empty( $args['append'] ) || !empty( $args['prepend'] ) ) {
            $doInputGroup = true;
        }
        if ( !empty( $doInputGroup ) ) { ?>
            <div class="input-group">
        <?php }
        if ( !empty( $args['prepend'] ) ) { ?>
            <div class="input-group-prepend">
                <span class="input-group-text"><?php echo $args['prepend']; ?></span>
            </div>
        <?php }
        switch( $type ) {
            case 'date':
            case 'email':
            case 'text':
                $type = self::makeElementProperty( $type, 'type' ); ?>
                <input autocomplete="off"<?php echo $type . self::getAttributes( $args ); ?>>
                <?php break;
            case 'textarea': ?>
                <textarea autocomplete="off"<?php echo self::getAttributes( $args ); ?>><?php echo $args['value'] ?? ''; ?></textarea>
                <?php break;
            case 'time':
                if ( !empty( $args['value'] ) ) {
                    $args['value'] = date( 'H:i', strtotime( $args['value'] ) );
                } ?>
                <input type="time" autocomplete="off"<?php echo self::getAttributes( $args ); ?>>
                <?php break;
            case 'number':
                $max = self::makeElementProperty( $args['max'] ?? 9999, 'max' );
                ?><input type="number" autocomplete="off" step="1"
                         min="0"<?php echo $max . self::getAttributes( $args ); ?>>
                <?php
                break;
            case 'currency':
                $args['value'] = number_format( $args['value'], 2, '.', '' ); ?>
                <input type="number" autocomplete="off" step="0.01" min="0" aria-label="Amount"<?php echo self::getAttributes( $args ); ?>>
                <?php break;
            case 'file':
                ?>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="customFile">
                    <label class="custom-file-label" for="customFile">Choose file</label>
                </div>
                <?php break;
        }
        if ( !empty( $args['append'] ) ) { ?>
            <div class="input-group-append">
                <?php echo $args['append']; ?>
            </div>
        <?php }
        if ( !empty( $doInputGroup ) ) {
            ?>
            </div>
        <?php }
        return ob_get_clean();
    }
}