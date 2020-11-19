<?php

namespace Phoenix\Page;

use Phoenix\Entity\EntityFactory;
use Phoenix\Form\GoToIDEntityForm;
use Phoenix\Messages;
use Phoenix\Page\ArchivePage\ArchivePageBuilder;
use Phoenix\Page\DetailPage\DetailPageBuilder;
use Phoenix\Page\MenuItems\MenuItemsEntities;
use Phoenix\PDOWrap;
use Phoenix\URL;

/**
 * Class PageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
abstract class EntityPageBuilder extends AdminPageBuilder
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
     * @return MenuItemsEntities
     */
    public function getMenuItems(): MenuItemsEntities
    {
        return new MenuItemsEntities( $this->getEntityFactory() );
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
     * @return GoToIDEntityForm
     */
    public function getGoToIDForm(): GoToIDEntityForm
    {
        return (
            new GoToIDEntityForm(
                $this->HTMLUtility,
                $this->getEntityFactory()->getNew()
            )
        )->makeFields();
    }

    /**
     * @param PDOWrap  $db
     * @param Messages $messages
     * @param URL      $url
     * @param string   $pageType
     * @param string   $entityType
     * @return EntityPageBuilder|null
     */
    /*
    public static function create(PDOWrap $db, Messages $messages, URL $url, string $pageType = '', string $entityType = ''): ?self
    {
        if ( $pageType === 'detail' ) {
            return DetailPageBuilder::create( $db, $messages, $url, $entityType );
        }
        return ArchivePageBuilder::create( $db, $messages, $url, $entityType );
    }
    */
}