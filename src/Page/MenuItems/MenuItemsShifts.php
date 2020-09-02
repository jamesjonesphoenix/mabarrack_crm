<?php

namespace Phoenix\Page\MenuItems;

/**
 * Class MenuItemsShifts
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class MenuItemsShifts extends MenuItemsEntities
{
    /**
     * @return array[]
     */
    public function getEntityMenuItems(): array
    {
        $archiveURL = $this->entityFactory->getNew()->getArchiveLink();
        $aWeekAgo = date( 'Y-m-d', strtotime( '-1 week' ) );
        $thisMonth = date( 'Y-m-01' );
        $thisYear = date( 'Y-01-01' );
        return [
            'Unfinished Shifts' => [
                'icon' => 'history fa-flip-horizontal',
                'text' => 'Unfinished Shifts',
                'url' => $archiveURL . '&query[time_finished]=null',
                'number' => $this->entityFactory->getCount( ['time_finished' => null] )
            ],
            'Week Shifts' => [
                'icon' => 'calendar-week',
                'text' => 'Last 7 Days' ,
                'url' => $archiveURL . '&limit=1000&query[date][value]=' . $aWeekAgo . '&query[date][operator]=>=',
                'number' => $this->entityFactory->getCount( ['date' => ['operator' => '>=', 'value' => $aWeekAgo]] )
            ],
            'Month Shifts' => [
                'icon' => 'calendar-alt',
                'text' => date('F'). ' Shifts',
                'url' => $archiveURL . '&limit=1000&query[date][value]=' . $thisMonth . '&query[date][operator]=>=',
                'number' => $this->entityFactory->getCount( ['date' => ['operator' => '>=', 'value' => $thisMonth]] )
            ],
            //&query[date][value]=2020-08-01&query[date][operator]=%3E=
            'Year Shifts' => [
                'icon' => 'calendar',
                'text' => date('Y') . ' Shifts',
                'url' => $archiveURL . '&limit=1000&query[date][value]=' . $thisYear . '&query[date][operator]=>=',
                'number' => $this->entityFactory->getCount( ['date' => ['operator' => '>=', 'value' => $thisYear]] )
            ]
        ];
    }

}