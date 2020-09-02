<?php

namespace Phoenix\Page\MenuItems;

/**
 * Class MenuItems
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class MenuItemsReports extends MenuItems
{
    /**
     * @return array[]
     */
    public function getMenuItems(): array
    {
        $url = 'index.php?page=report&date_start='
            . date( 'Y', strtotime( 'last year' ) ) . '-07-01'
            . '&date_finish='
            . date( 'Y' ) . '-06-30'
            . '&report=';
        return [
            [
                'icon' => 'dollar-sign',
                'text' => 'Profit/Loss',
                'url' => $url . 'profit_loss',
            ], [
                'icon' => 'chart-bar',
                'text' => 'Activity Summary',
                'url' => $url . 'activity_summary'
            ]
        ];
    }
}