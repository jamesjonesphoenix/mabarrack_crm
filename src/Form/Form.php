<?php


namespace Phoenix\Form;


use Phoenix\Utility\FormFields;

/**
 * Class Form
 *
 * @author James Jones
 * @package Phoenix\Form
 *
 */
abstract class Form
{
    /**
     * @var array
     */
    public array $fields = [];

    /**
     * @var FormFields
     */
    public FormFields $htmlUtility;

    /**
     * HTML id property of <form> element
     *
     * @var string
     */
    public string $formID = '';

    /**
     * Form constructor.
     *
     * @param FormFields $htmlUtility
     */
    public function __construct(FormFields $htmlUtility)
    {
        $this->htmlUtility = $htmlUtility;
    }

    /**
     * @return string
     */
    abstract public function render(): string;

    /**
     * @return $this
     */
    abstract public function makeFields(): self;

    /**
     * Recursive method for adding hidden fields from URL query string
     *
     * @param array $inputArgs
     * @param array $prefixes
     * @return $this
     */
    public function makeHiddenFields(array $inputArgs = [], array $prefixes = []): self
    {
        $inputArgPrefix = '';
        $inputKeyPrefix = '';
        foreach ( $prefixes as $prefix ) {
            if ( empty( $loopedOnce ) ) {
                $inputArgPrefix = $prefix;
                $loopedOnce = true;
                continue;
            }
            $inputKeyPrefix .= $prefix .'_';
            $inputArgPrefix .= '[' . $prefix . ']';
        }
        foreach ( $inputArgs as $inputArgName => $inputArgValue ) {
            if ( is_iterable( $inputArgValue ) ) {
                $this->makeHiddenFields( $inputArgValue, array_merge( $prefixes, [$inputArgName] ) );
                continue;
            }
            if ( isset( $this->fields[$inputArgName] ) ) {
                continue;
            }

            if ( !empty( $inputArgPrefix ) ) {
                $inputArgName = $inputArgPrefix . '[' . $inputArgName . ']';
            }


            $this->fields['hidden'][$inputKeyPrefix . $inputArgName] = $this->htmlUtility::getHiddenFieldHTML( [
                'name' => $inputArgName,
                'value' => $inputArgValue,
            ] );
        }
        return $this;
    }
}