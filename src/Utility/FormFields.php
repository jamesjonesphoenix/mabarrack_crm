<?php


namespace Phoenix\Utility;


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
        $args = self::mergeDefaultArgs( $args, 'checkbox' );
        $args['class'] = str_replace( 'form-control', 'custom-control-input', $args['class'] );
        $attributes = self::getAttributes( $args ) . self::makeElementProperty( $args['checked'] ?? '', 'checked' );
        ob_start(); ?>
        <div class="custom-control custom-checkbox mb-2"><input type="checkbox"<?php echo $attributes; ?> >
            <label class="custom-control-label" for="<?php echo $args['id']; ?>"><?php echo $args['label']; ?><small><?php echo $args['small'] ?? ''; ?></small></label>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @param string $item
     * @return string
     */
    private function inputGroupText(string $item = ''): string
    {
        if ( strlen( strip_tags( $item ) ) === strlen( $item ) ) {
            return '<span class="input-group-text">'
                . $item
                . '</span>';
        }
        return $item;
    }

    /**
     * @param string $content
     * @param string $prepend
     * @param string $append
     * @return string
     */
    public static function wrapInputGroup(string $content = '', string $prepend = '', string $append = ''): string
    {
        if ( empty( $prepend ) && empty( $append ) ) {
            return $content;
        }

        ob_start(); ?>
        <div class="input-group">
            <?php if ( !empty( $prepend ) ) { ?>
                <div class="input-group-prepend">
                    <?php echo self::inputGroupText( $prepend ); ?>
                </div>
            <?php }
            echo $content;
            if ( !empty( $append ) ) { ?>
                <div class="input-group-append">
                    <?php echo self::inputGroupText( $append ) ?? ''; ?>
                </div>
            <?php } ?>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @param array  $args
     * @param string $type
     * @return array
     */
    public static function mergeDefaultArgs(array $args = [], string $type = ''): array
    {
        $args['class'] = ($args['class'] ?? '') . ' form-control';
        return parent::mergeDefaultArgs( $args, $type );
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getOptionDropdownFieldHTML(array $args = []): string
    {
        $args = self::mergeDefaultArgs( $args, 'options_dropdown' );
        $args['class'] .= ' custom-select';
        $options = $args['options'] ?? [];
        if ( !empty( $args['placeholder'] ) && !array_key_exists( '', $options ) ) {
            $options = ['' => $args['placeholder']] + $options;
        }
        ob_start(); ?>
        <select autocomplete="off"<?php echo self::getAttributes( $args ); ?>>
            <?php foreach ( $options as $optionValue => $optionString ) {
                // $optionString = is_string( $optionArgs ) ? $optionArgs : $optionArgs['content'];
                $selected = ($args['selected'] ?? '') === $optionValue ? self::makeElementProperty( 'selected', 'selected' ) : ''; ?>
                <option<?php echo self::makeElementProperty( $optionValue, 'value' )
                    . $selected; ?>><?php echo str_replace( '_', ' ', $optionString ) ?></option>
            <?php } ?>
        </select>
        <?php return self::getFieldLabelHTML(
            $args['label'] ?? '',
            $args['id']
        ) . self::wrapInputGroup(
            ob_get_clean(),
            $args['prepend'] ?? '',
            $args['append'] ?? ''
        );
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getHiddenFieldHTML(array $args = []): string
    {
        $args = self::mergeDefaultArgs( $args, 'hidden' );
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
        //$type = 'text';
        ob_start();
        //$type
        echo self::getFieldLabelHTML(
            $args['label'] ?? '',
            $args['id']
        );
        ?><input autocomplete="off" type="text"<?php echo self::getAttributes( $args ); ?>><?php
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
        $suffix = '-2';

        return self::getFieldLabelHTML(
                $args['label'] ?? '',
                $args['id']
            ) . self::wrapInputGroup(
                self::getTextFieldHTML( [
                    'id' => $args['id'],
                    'name' => $args['name'],
                    'placeholder' => 'Enter Password',
                    'disabled' => $args['disabled']
                ] ) . self::getTextFieldHTML( [
                    'id' => $args['id'] . $suffix,
                    'name' => $args['name'] . $suffix,
                    'placeholder' => 'Confirm Password',
                    'disabled' => $args['disabled']
                ] ),
                self::getButton( [
                    'type' => 'button',
                    'id' => 'change-password-button',
                    'class' => 'btn btn-info' . ($args['disabled'] ? '' : ' d-none'),
                    'content' => 'Change Password',
                    'disabled' => true
                ] )
            );
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

        ob_start();


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
            case 'file': ?>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="customFile">
                    <label class="custom-file-label" for="customFile">Choose file</label>
                </div>
                <?php break;
        }
        return self::getFieldLabelHTML(
                $args['label'] ?? '',
                $args['id']
            ) . self::wrapInputGroup( ob_get_clean(), $args['prepend'] ?? '', $args['append'] ?? '' );
    }
}