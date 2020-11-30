<?php

namespace Phoenix\Page\MenuItems;

/**
 * Class MenuItemsUsers
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class MenuItemsUsers extends MenuItemsEntities
{


    /**
     * @return array[]
     */
    public function getEntityMenuItems(): array
    {
        $archiveURL = $this->entityFactory->getNew()->getArchiveLink();
        return [
            'workers' => [
                'icon' => 'user-clock',
                'content' => 'Workers',
                'href' => $archiveURL . '&query[type]=staff',
                'number' => $this->entityFactory->getCount( ['type' => 'staff'] )
            ],
            'admins' => [
                'icon' => 'user-cog',
                'content' => 'Admins',
                'href' => $archiveURL . '&query[type]=admin',
                'number' => $this->entityFactory->getCount( ['type' => 'admin'] )
            ]
        ];
    }

}