<?php


namespace Phoenix\Form;

use Phoenix\Entity\Entity;
use Phoenix\Utility\FormFields;

/**
 * Class EntityForm
 *
 * @package Phoenix
 */
abstract class EntityForm extends Form
{
    /**
     * @var Entity
     */
    public Entity $entity;

    /**
     * EntityForm constructor.
     *
     * @param FormFields $htmlUtility
     * @param Entity     $entity
     */
    public function __construct(FormFields $htmlUtility, Entity $entity)
    {
        $this->entity = $entity;
        $this->htmlUtility = $htmlUtility;
    }
}