<?php


namespace Phoenix\Page\MenuItems;

/**
 * Class MenuItems
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
abstract class MenuItems
{
    /**
     * @return array[]
     */
    abstract public function getMenuItems(): array;
}