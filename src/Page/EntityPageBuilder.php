<?php

namespace Phoenix\Page;

use Phoenix\Entity\EntityFactory;
use Phoenix\Form\GoToIDForm;
use Phoenix\Page\MenuItems\MenuItems;

/**
 * Class PageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
abstract class EntityPageBuilder extends PageBuilder
{

    /**
     * @var EntityFactory
     */
    protected EntityFactory $entityFactory;

    /**
     * @return EntityFactory
     */
    abstract protected function getNewEntityFactory(): EntityFactory;

    /**
     * @return MenuItems
     */
    public function getMenuItems(): MenuItems
    {
        return new MenuItems( $this->getEntityFactory() );
    }

    /**
     * @return EntityFactory
     */
    protected function getEntityFactory(): EntityFactory
    {
        if ( !empty( $this->entityFactory ) ) {
            return $this->entityFactory;
        }
        return $this->entityFactory = $this->getNewEntityFactory();
    }

    /**
     * @return GoToIDForm
     */
    public function getGoToIDForm(): GoToIDForm
    {
        return (new GoToIDForm( $this->HTMLUtility, $this->getEntityFactory()->getNew() ))->makeFields();
    }
}