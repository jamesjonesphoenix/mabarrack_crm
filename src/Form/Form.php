<?php


namespace Phoenix\Form;

use Phoenix\Entity\Entity;
use Phoenix\Utility\FormFields;

/**
 * Class Form
 *
 * @package Phoenix
 */
abstract class Form
{
    /**
     * @var array
     */
    public array $fields = [];

    /**
     * @var Entity
     */
    public Entity $entity;

    /**
     * HTML id property of <form> element
     *
     * @var string
     */
    public string $formID = '';

    /**
     * @var FormFields
     */
    public FormFields $htmlUtility;

    /**
     * Form constructor.
     *
     * @param FormFields $htmlUtility
     * @param Entity     $entity
     */
    public function __construct(FormFields $htmlUtility, Entity $entity)
    {
        $this->entity = $entity;
        $this->htmlUtility = $htmlUtility;
    }

    /**
     * @return string
     */
    abstract public function render(): string;
}