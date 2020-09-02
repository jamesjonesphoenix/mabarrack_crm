<?php

namespace Phoenix\Page\ArchivePage;

use Phoenix\Entity\SettingFactory;
use Phoenix\Report\Archive\ArchiveTableSettings;

/**
 * Class PageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class ArchivePageBuilderSettings extends ArchivePageBuilder
{
    protected function getNewEntityFactory(): SettingFactory
    {
        return new SettingFactory( $this->db, $this->messages );
    }

    /**
     * @return ArchiveTableSettings
     */
    protected function getNewArchiveTableReport(): ArchiveTableSettings
    {
        return new ArchiveTableSettings($this->HTMLUtility, $this->format);
    }
}