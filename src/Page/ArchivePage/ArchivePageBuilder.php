<?php


namespace Phoenix\Page\ArchivePage;

use Phoenix\Entity\Entities;
use Phoenix\Entity\Entity;
use Phoenix\Form\GroupByEntityForm;
use Phoenix\Page\EntityPageBuilder;
use Phoenix\Report\Archive\ArchiveTable;

/**
 * Class ArchivePageBuilder
 *
 * @author James Jones
 * @package Phoenix\ArchivePage
 *
 */
abstract class ArchivePageBuilder extends EntityPageBuilder
{
    /**
     * @var Entity[]
     */
    protected array $entities;

    /**
     * @var array
     */
    protected array $provisionArgs = [];

    /**
     * @var string
     */
    private string $groupBy = '';

    /**
     * @var mixed|ArchiveTable|null
     */
    private $archiveTableReport;

    /**
     * @var ArchivePage
     */
    protected ArchivePage $page;

    /**
     * @var array
     */
    protected array $inputArgs = [];

    /**
     * @var array
     */
    protected array $queryArgs = [];

    /**
     * @var bool
     */
    private bool $errorEntitiesOnly = false;

    /**
     * @return Entity[]
     */
    public function getEntities(): array
    {
        //d($inputArgs);
        if ( !empty( $this->entities ) ) {
            return $this->entities;
        }
        $queryArgs = $this->inputArgs['query'] ?? [];
        foreach ( ['order_by', 'limit'] as $argName ) {
            if ( !empty( $this->inputArgs[$argName] ) ) {
                $queryArgs[$argName] = $this->inputArgs[$argName];
            }
        }
        $entities = $this->getEntityFactory()->getEntities( $queryArgs, $this->provisionArgs );

        if ( $this->errorEntitiesOnly ) {
            $entities = (new Entities( $entities ))->getEntitiesWithErrors();
        }

//d($entities);
        return $this->entities = $entities;

    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage()
            ->setEntity(
                $this->getEntityFactory()->getNew()
            )->setNavLinks(
                ($this->getMenuItems())->getMenuItems()
            );
        $this->addArchives();
        $this->addTitle();

        return $this;
    }

    /**
     * @param array $inputArgs
     * @return $this
     */
    public function setInputArgs(array $inputArgs = []): self
    {
        $this->inputArgs['query'] = $this->queryArgs;
        foreach ( $inputArgs as $inputArgName => $inputArgValue ) {
            switch( $inputArgName ) {
                case 'query':
                    $columnNames = array_keys( $this->getEntityFactory()->getNew()->columns );
                    foreach ( $inputArgValue as $queryArgName => $queryArgValue ) {
                        if ( in_array( $queryArgName, $columnNames, true ) ) {
                            $this->inputArgs['query'][$queryArgName] = $queryArgValue;
                        }
                    }
                    break;
                case 'group_by':
                    $this->groupBy = $inputArgValue;
                    break;
                case 'errors_only':
                    $this->errorEntitiesOnly = true;
                    break;
                default:
                    $this->inputArgs[$inputArgName] = $inputArgValue;
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    private function addTitle(): self
    {
        $prefix = $this->getTitlePrefix();
        $space = empty( $prefix ) ? '' : ' ';
        $this->page->setTitle(
            $prefix
            . $space
            . ucfirst( $this->getEntityFactory()->getEntityNamePlural() )
            . ($this->errorEntitiesOnly ? ' With Errors' : '')
        );
        return $this;
    }

    /**
     * @return string
     */
    protected function getTitlePrefix(): string
    {
        $icon = $this->entityFactory->getNew()->getIcon();
        if ( empty( $this->inputArgs['query'] ) || $this->inputArgs['query'] === $this->queryArgs ) {
            if ( empty( $this->inputArgs['limit'] ) ) {
                if ( $this->errorEntitiesOnly ) {
                    return $icon;
                }
                return $icon . ' All';
            }
            if ( !empty( $this->inputArgs['order_by'] ) && strpos( $this->inputArgs['order_by'], 'date' ) !== false ) {
                return $icon . ' Recent';
            }
        }

        return $icon;
    }

    protected function getArchiveTableReport(): ArchiveTable
    {
        if ( !empty( $this->archiveTableReport ) ) {
            return $this->archiveTableReport;
        }
        return $this->archiveTableReport = $this->getNewArchiveTableReport();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function addArchives(): self
    {
        $this->page->setArchives(
            $this->getArchiveTableReport()
                ->setEntities(
                    $this->getEntities(),
                    $this->getEntityFactory()->getNew(),
                )->setGroupByForm(
                    (new GroupByEntityForm( $this->HTMLUtility, $this->getEntityFactory()->getNew() ))
                        ->makeHiddenFields( $this->inputArgs ),
                    $this->groupBy
                )->setGoToIDForm(
                    $this->getGoToIDForm(),
                )->hideInessentialColumns( $this->errorEntitiesOnly )
                ->render()
        );
        return $this;
    }


    /**
     * @return ArchivePage
     */
    protected function getNewPage(): ArchivePage
    {
        return new ArchivePage( $this->HTMLUtility );
    }

    /**
     * @return mixed
     */
    abstract protected function getNewArchiveTableReport(): ArchiveTable;


}