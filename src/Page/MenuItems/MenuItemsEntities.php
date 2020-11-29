<?php


namespace Phoenix\Page\MenuItems;


use Phoenix\Entity\EntityFactory;
use Phoenix\Utility\HTMLTags;

/**
 * Class MenuItemsEntities
 *
 * @author James Jones
 * @package Phoenix\Page\MenuItems
 *
 */
class MenuItemsEntities extends MenuItems
{
    /**
     * @var EntityFactory
     */
    protected EntityFactory $entityFactory;

    /**
     * @var int
     */
    protected int $maxErrorsToCheck = 1000;

    /**
     * @var array|bool
     */
    protected $provisionArgsForHealthCheck = false;

    /**
     * @var bool
     */
    private bool $includeAddNew = false;


    /**
     * MenuItems constructor.
     *
     * @param EntityFactory $entityFactory
     */
    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
        $this->icon = $this->entityFactory->getNew()->getIcon();
    }

    /**
     * @return string
     */
    public function getContextualClass(): string
    {
        return $this->entityFactory->getEntityName();
    }

    /**
     * @return int
     */
    public function hasErrors(): int
    {
        $sessionKey = 'entities_with_errors';
        $entityName = $this->entityFactory->getEntityName();
        if ( isset( $_SESSION[$sessionKey][$entityName] ) ) {
            return $_SESSION[$sessionKey][$entityName];
        }
        foreach ( $this->entityFactory->getEntities( [], $this->provisionArgsForHealthCheck ) as $entity ) {
            if ( !empty( $entity->healthCheck() ) ) {
                return $_SESSION[$sessionKey][$entityName] = true;
            }
        }
        return $_SESSION[$sessionKey][$entityName] = false;
    }

    /**
     * @return $this
     */
    public function includeAddNew(): self
    {
        $this->includeAddNew = true;
        return $this;
    }

    /**
     * @param bool $countErrors
     * @return array[]
     */
    public function getMenuItems($countErrors = false): array
    {
        $entity = $this->entityFactory->getNew();
        $entityNamePlural = ucwords( $entity->entityNamePlural );

        if ( $this->includeAddNew && $entity->canCreate() ) {
            $return['add_new'] = [
                'icon' => 'plus',
                'content' => 'Add New ' . ucwords( $entity->entityName ),
                'href' => $entity->getLink(false)
            ];
        }
        $return = array_merge(
            $return ?? [],
            $this->getEntityMenuItems()
        );

        $return['all_items'] = [
            'icon' => 'list',
            'content' => 'All ' . $entityNamePlural,
            'href' => $entity->getArchiveLink(),
            'number' => $this->entityFactory->countAll()
        ];
        if ( $this->hasErrors() ) {
            $return['with_errors'] = [
                'icon' => 'bug',
                'content' => $entityNamePlural . ' With Errors',
                'href' => $entity->getArchiveLink() . '&errors_only=true',
            ];
        }
        return $return;
    }

    /**
     * @return array
     */
    public function getEntityMenuItems(): array
    {
        return [];
    }
}