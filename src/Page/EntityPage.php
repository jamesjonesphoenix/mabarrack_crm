<?php


namespace Phoenix\Page;


use Phoenix\Entity\Entity;
use Phoenix\Form\GoToIDEntityForm;

/**
 * Class EntityPage
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
abstract class EntityPage extends Page
{
    /**
     * @var Entity
     */
    protected Entity $entity;



    /**
     * @param Entity $entity
     * @return $this
     */
    public function setEntity(Entity $entity): self
    {
        $this->entity = $entity;
        return $this;
    }

}