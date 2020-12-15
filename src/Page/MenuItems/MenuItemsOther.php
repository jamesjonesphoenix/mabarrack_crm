<?php

namespace Phoenix\Page\MenuItems;

use Phoenix\Utility\HTMLTags;

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
     * @var string
     */
    protected string $icon = 'user-cog';

    /**
     * @return string
     */
    public function getContextualClass(): string
    {
        return 'secondary';
    }

    /**
     * @return array[]
     */
    public function getMenuItems(): array
    {
        return [
            [
                'icon' => 'user-clock',
                'content' => 'Employee Dashboard',
                'href' => 'employee.php',
            ], [
                'icon' => 'cogs',
                'content' => 'Settings',
                'href' => 'index.php?page=archive&entity=settings'
            ]
        ];
    }
}