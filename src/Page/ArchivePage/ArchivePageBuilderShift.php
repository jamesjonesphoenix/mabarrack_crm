<?php

namespace Phoenix\Page\ArchivePage;

use Phoenix\Entity\ShiftFactory;
use Phoenix\Page\MenuItems\MenuItemsShifts;
use Phoenix\Report\Archive\ArchiveTableShifts;

/**
 * Class ArchivePageBuilderShift
 *
 * @author James Jones
 * @package Phoenix\ArchivePage
 *
 */
class ArchivePageBuilderShift extends ArchivePageBuilder
{
    /**
     * @var array
     */
    protected array $provisionArgs = [
        'activity' => true,
        'worker' => ['shifts' => false],
        'furniture' => false,
        'job' => false
    ];

    /**
     * @return MenuItemsShifts
     */
    public function getMenuItems(): MenuItemsShifts
    {
        return new MenuItemsShifts( $this->getEntityFactory() );
    }

    /**
     * @return ShiftFactory
     */
    protected function getNewEntityFactory(): ShiftFactory
    {
        return new ShiftFactory( $this->db, $this->messages );
    }

    /**
     * @return ArchiveTableShifts
     */
    protected function getNewArchiveTableReport(): ArchiveTableShifts
    {
        return new ArchiveTableShifts( $this->HTMLUtility, $this->format);
    }

    /**
     * @return string
     */
    protected function getTitlePrefix(): string
    {
        $icon = $this->entityFactory->getNew()->getIcon();
        $timeFinished = $this->inputArgs['query']['time_finished'] ?? '';
        if ( is_string( $timeFinished ) && strtolower( $timeFinished ) === 'null' ) {
            return $icon . ' Unfinished';
        }
        $date = $this->inputArgs['query']['date'] ?? [];
        if ( !empty( $date['operator'] ) && $date['operator'] === '>' ) {
            if ( $date['value'] === date( 'Y-01-01' ) ) {
                return $icon . ' ' . date( 'Y' );
            }
            if ( $date['value'] === date( 'Y-m-01' ) ) {
                return $icon . ' ' . date( 'F' );
            }
            if ( $date['value'] === date( 'Y-m-d', strtotime( '-1 week' ) )) {
                return $icon . ' Last 7 Days of' ;
            }
        }
        return parent::getTitlePrefix();
    }
}