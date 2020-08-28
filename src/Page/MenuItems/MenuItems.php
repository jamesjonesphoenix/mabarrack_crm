<?php


namespace Phoenix\Page\MenuItems;


use Phoenix\Entity\EntityFactory;

/**
 * Class MenuItems
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class MenuItems
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
     * MenuItems constructor.
     *
     * @param EntityFactory $entityFactory
     */
    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    /**
     * @return int
     */
    public function hasErrors(): int
    {
        foreach ( $this->entityFactory->getEntities( [], $this->provisionArgsForHealthCheck ) as $entity ) {
            if ( !empty( $entity->healthCheck() ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param bool $countErrors
     * @return array[]
     */
    public function getMenuItems($countErrors = false): array
    {
        $entity = $this->entityFactory->getNew();
        $entityNamePlural = ucwords( $entity->entityNamePlural );

        $return['all_items'] = [
            'icon' => 'list',
            'text' => 'All ' . $entityNamePlural,
            'url' => $entity->getArchiveLink(),
            'number' => $this->entityFactory->countAll()
        ];
        if ( $this->hasErrors() ) {
            $return['with_errors'] = [
                'icon' => 'bug',
                'text' => $entityNamePlural . ' With Errors',
                'url' => $entity->getArchiveLink() . '&errors_only=true',
            ];
        }
        return array_merge( $this->getEntityMenuItems(), $return );
    }

    /**
     * @return array
     */
    public function getEntityMenuItems(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getMenuArray(): array
    {
        $entity = $this->entityFactory->getNew();
        return [
            'icon' => $entity->getIcon(),
            'contextual_class' => $entity->entityName,
            'items' => $this->getMenuItems()
        ];
    }
}