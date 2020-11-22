<?php

namespace Phoenix\Page\MenuItems;

/**
 * Class MenuItemsOther
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class MenuItemsOther extends MenuItems
{
    /**
     * @return array[]
     */
    public function getMenuItems(): array
    {
        return [
            [
                'icon' => 'user-clock',
                'content' => 'Worker Dashboard',
                'href' => 'worker.php',
            ], [
                'icon' => 'cogs',
                'content' => 'Settings',
                'href' => 'index.php?page=archive&entity=settings'
            ]
        ];
    }
}