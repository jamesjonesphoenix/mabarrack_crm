<?php


namespace Phoenix\Page\ArchivePage;

use Phoenix\Entity\Entities;
use Phoenix\Entity\Entity;
use Phoenix\Form\GroupByEntityForm;
use Phoenix\Messages;
use Phoenix\Page\EntityPageBuilder;
use Phoenix\PDOWrap;
use Phoenix\Report\Archive\ArchiveTable;
use Phoenix\URL;

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
     * @var Entities
     */
    protected Entities $entities;

    /**
     * @var array
     */
    protected array $provisionArgs = [];

    /**
     * @var mixed|ArchiveTable|null
     */
    private $archiveTableReport;

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
     * @return Entities
     */
    public function getEntities(): Entities
    {
        if ( !empty( $this->entities ) ) {
            return $this->entities;
        }
        $queryArgs = $this->inputArgs['query'] ?? [];
        foreach ( ['order_by', 'limit'] as $argName ) {
            if ( !empty( $this->inputArgs[$argName] ) ) {
                $queryArgs[$argName] = $this->inputArgs[$argName];
            }
        }
        $entities = new Entities(
            $this->getEntityFactory()->getEntities( $queryArgs, $this->provisionArgs )
        );
        if ( $this->errorEntitiesOnly ) {
            $entities = $entities->getEntitiesWithErrors();
        }

        return $this->entities = $entities;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage()
            ->setNavLinks(
                ($this->getMenuItems())->getMenuItems()
            )->setHeadTitle( ucwords( $this->getEntityFactory()->getEntityNamePlural() ) );
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
                case 'errors_only':
                    $this->errorEntitiesOnly = true;
                    break;
                default:
                    $this->inputArgs[$inputArgName] = $inputArgValue;
            }
        }
        return parent::setInputArgs( $inputArgs );
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
        )->showTitleWhenPrinting();
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
        // return $this->archiveTableReport = $this->getNewArchiveTableReport();

        return $this->archiveTableReport = $this->getReportClient()->getFactory()->archiveTables()
            ->get(
                $this->getEntityFactory()->getEntityName()
            );
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function addArchives(): self
    {
        $dummyEntity = $this->getEntityFactory()->getNew();

        $report = $this->getArchiveTableReport()
            ->setEntities(
                $this->getEntities()
            )
            ->setDummyEntity(
                $dummyEntity
            )
            ->setGroupByForm(
                (new GroupByEntityForm( $this->HTMLUtility, $dummyEntity, $this->getURL() )),
                $this->groupBy
            )
            ->setGoToIDForm( $this->getGoToIDForm() )
            ->setEmptyMessage( 'No ' . $this->getEntityFactory()->getEntityNamePlural() . ' found to report.' )
            ->disableCollapseButton();

        if ( $this->errorEntitiesOnly ) {
            $report
                ->hideInessentialColumns()
                ->editColumn( 'errors', ['hidden' => false] )
                ->setEmptyMessageClass( 'success' )
                ->setEmptyMessage( 'No ' . $dummyEntity->entityNamePlural . ' found with errors.' );
        }

        $this->page->addContent( $report->render() );
        return $this;
    }

    /**
     * @return mixed
     */
    // abstract protected function getNewArchiveTableReport(): ArchiveTable;

    /**
     * @param PDOWrap  $db
     * @param Messages $messages
     * @param URL      $url
     * @param string   $entityType
     * @return static|null
     */
    public static function create(PDOWrap $db, Messages $messages, URL $url, string $entityType = ''): ?self
    {
        switch( $entityType ) {
            case 'customer':
            case 'customers':
                return new ArchivePageBuilderCustomer( $db, $messages, $url );
            case 'furniture':
                return new ArchivePageBuilderFurniture( $db, $messages, $url );
            case 'job':
            case 'jobs':
                return new ArchivePageBuilderJob( $db, $messages, $url );
            case 'shift':
            case 'shifts':
                return new ArchivePageBuilderShift( $db, $messages, $url );
            case 'user':
            case 'users':
                return new ArchivePageBuilderUser( $db, $messages, $url );
            case 'setting':
            case 'settings':
                return new ArchivePageBuilderSettings( $db, $messages, $url );
        }
        return null;

    }
}