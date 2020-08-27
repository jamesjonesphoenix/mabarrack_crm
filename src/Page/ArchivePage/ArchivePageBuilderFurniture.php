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

    /**
     * @return ArchiveTableFurniture
     */
    protected function getNewArchiveTableReport(): ArchiveTableFurniture
    {
        return new ArchiveTableFurniture($this->HTMLUtility, $this->format, $this->messages);
    }
}