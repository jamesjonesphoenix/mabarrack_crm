<?php


namespace Phoenix\Page\ArchivePage;


use Phoenix\Page\EntityPage;

/**
 * Class ArchivePage
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class ArchivePage extends EntityPage
{
    /**
     * @var string
     */
    private string $archivesHTML = '';

    /**
     * @param string $archivesHTML
     * @return $this
     */
    public function setArchives(string $archivesHTML = ''): self
    {
        $this->archivesHTML = $archivesHTML;
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public
    function renderBody(): string
    {
       return $this->archivesHTML;
    }

    /**
     * @return string
     */
    public function getPageHeadTitle(): string
    {
        return ucwords( $this->entity->entityNamePlural );
    }

    /**
     * @return string
     */
    public function getBodyClasses(): string
    {
        return 'archive-page ' . parent::getBodyClasses();
    }
}