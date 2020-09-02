<?php


namespace Phoenix\Page\ArchivePage;


use Phoenix\Entity\UserFactory;
use Phoenix\Page\MenuItems\MenuItemsUsers;
use Phoenix\Report\Archive\ArchiveTableUsers;

/**
 * Class ArchivePageBuilderUser
 *
 * @author James Jones
 * @package Phoenix\ArchivePage
 *
 */
class ArchivePageBuilderUser extends ArchivePageBuilder
{
    /**
     * @var array
     */
    protected array $provisionArgs = [
        'shifts' => false
        /*
            [
            'activity' => false,
            'furniture' => false,
            'worker' => ['shifts' => false],
            'job' => false
        ]
        */
    ];

    /**
     * @return string
     */
    protected function getTitlePrefix(): string
    {
        $role = $this->inputArgs['query']['type'] ?? '';
        if ( !empty( $role ) ) {
            if ( $role === 'admin' ) {
                return $this->HTMLUtility::getIconHTML( 'user-cog' ) . ' Admin';
            }
            if ( $role === 'staff' ) {
                return $this->HTMLUtility::getIconHTML( 'user-clock' ) . ' Worker';
            }
        }

        return parent::getTitlePrefix();
    }

    /**
     * @return UserFactory
     */
    protected function getNewEntityFactory(): UserFactory
    {
        return new UserFactory( $this->db, $this->messages );
    }

    /**
     * @return MenuItemsUsers
     */
    public function getMenuItems(): MenuItemsUsers
    {
        return new MenuItemsUsers( $this->getEntityFactory() );
    }

    /**
     * @return ArchiveTableUsers
     */
    protected function getNewArchiveTableReport(): ArchiveTableUsers
    {
        return new ArchiveTableUsers( $this->HTMLUtility, $this->format );
    }
}