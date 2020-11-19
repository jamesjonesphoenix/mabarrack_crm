<?php


namespace Phoenix\Page\ArchivePage;

use Phoenix\Entity\FurnitureFactory;
use Phoenix\Report\Archive\ArchiveTableFurniture;

/**
 * Class ArchivePageBuilderFurniture
 *
 * @author James Jones
 * @package Phoenix\ArchivePage
 *
 */
class ArchivePageBuilderFurniture extends ArchivePageBuilder
{
    /**
     * @return FurnitureFactory
     */
    protected function getNewEntityFactory(): FurnitureFactory
    {
        return new FurnitureFactory( $this->db, $this->messages );
    }
}