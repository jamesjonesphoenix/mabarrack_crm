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
                'text' => 'Worker Dashboard',
                'url' => 'worker.php',
            ], [
                'icon' => 'cogs',
                'text' => 'Settings',
                'url' => 'index.php?page=archive&entity=settings'
            ]
        ];
    }
}