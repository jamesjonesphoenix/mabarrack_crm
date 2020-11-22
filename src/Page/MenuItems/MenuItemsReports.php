<?php

namespace Phoenix\Page\MenuItems;

use Phoenix\URL;

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
        $menuItems = [
            [
                'icon' => 'dollar-sign',
                'content' => 'Profit/Loss',
                'href' => 'profit_loss',
            ], [
                'icon' => 'chart-bar',
                'content' => 'Activity Summary',
                'href' => 'activity_summary'
            ], [
                'icon' => 'chart-bar',
                'content' => 'Worker Week',
                'href' => 'worker_week'
            ]
        ];
        $defaultDates = [
            'date_start' => date( 'Y', strtotime( 'last year' ) ) . '-07-01',
            'date_finish' => date( 'Y' ) . '-06-30',
        ];
        $url = (new URL())
            ->reset()
            ->setQueryArg( 'page', 'report' );
        foreach ( $menuItems as &$item ) {
            if ( $item['href'] !== 'worker_week' ) {
                $url->setQueryArgs( $defaultDates );
            } else {
                $url->removeQueryArgs( array_keys( $defaultDates ) );

            }
            $item['href'] = $url->setQueryArg( 'report', $item['href'] )->write();
        }
        return $menuItems;
    }
}