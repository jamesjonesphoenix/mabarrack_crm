<?php


namespace Phoenix\Form;


use Phoenix\Utility\FormFields;

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
     * @return string
     */
    abstract public function render(): string;

    /**
     * @return $this
     */
    abstract public function makeFields(): self;
}