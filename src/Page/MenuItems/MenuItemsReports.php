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
                'text' => 'Profit/Loss',
                'url' => 'profit_loss',
            ], [
                'icon' => 'chart-bar',
                'text' => 'Activity Summary',
                'url' => 'activity_summary'
            ], [
                'icon' => 'chart-bar',
                'text' => 'Worker Week',
                'url' => 'worker_week'
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
            if ( $item['url'] !== 'worker_week' ) {
                $url->setQueryArgs( $defaultDates );
            } else {
                $url->removeQueryArgs( array_keys( $defaultDates ) );

            }
            $item['url'] = $url->setQueryArg( 'report', $item['url'] )->write();
        }
        return $menuItems;
    }
}