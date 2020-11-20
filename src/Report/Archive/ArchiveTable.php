<?php


namespace Phoenix\Report\Archive;

use Phoenix\Entity\Entities;
use Phoenix\Entity\Entity;
use Phoenix\Form\GoToIDEntityForm;
use Phoenix\Form\GroupByEntityForm;
use Phoenix\Format;
use Phoenix\Report\Report;
use Phoenix\URL;
use Phoenix\Utility\HTMLTags;

/**
 * Class ArchiveTable
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
abstract class ArchiveTable extends Report
{
    /**
     * @var Entities
     */
    protected Entities $entities;

    /**
     * @var string
     */
    private string $groupByForm = '';

    /**
     * @var string
     */
    private string $goToIDForm = '';

    /**
     * @var Entity
     */
    private Entity $entity;

    /**
     * @var bool
     */
    protected bool $includeColumnToggles = true;

    /**
     * @var bool
     */
    protected bool $printButton = true;

    /**
     * @var string
     */
    protected string $tableClass = 'archive table-sorter';

    /**
     * @var string
     */
    protected string $emptyMessage = 'No items found to report.';

    /**
     * Report constructor.
     *
     * @param HTMLTags $htmlUtility
     * @param Format   $format
     * @param URL      $url
     */
    public function __construct(HTMLTags $htmlUtility, Format $format, URL $url)
    {
        $this->columns = array_merge(
            [
                'id' => []
            ], $this->columns, [
                'errors' => [
                    'title' => 'Errors',
                    'remove_if_empty' => true,
                    'default' => '&minus;',
                    'hidden' => true
                ],
                'view' => ['title' => '']
            ]
        );
        if ( empty( $columns['id']['title'] ) ) {
            $this->columns['id']['title'] = 'ID';
        }

        parent::__construct( $htmlUtility, $format, $url );

    }

    /**
     * @param Entities|null $entities
     * @return $this
     */
    public function setEntities(Entities $entities = null): self
    {
        if ( $entities === null ) {
            return $this;
        }
        $this->entities = $entities;
        if ( $entities->getCount() > 1 ) {
            $entity = $entities->getOne();
            if ( $entity !== null ) {
                $this->entity = $entity;
            }
        }
        return $this;
    }

    /**
     * @param GroupByEntityForm $groupByForm
     * @param string            $groupedBy
     * @return $this
     */
    public function setGroupByForm(GroupByEntityForm $groupByForm, string $groupedBy = ''): self
    {
        foreach ( $this->getColumns( 'title' ) as $key => $column ) {
            if ( !empty( $key ) && !empty( $column ) ) {
                $columns[$key] = $column;
            }
        }
        $this->groupedBy = array_key_exists( $groupedBy, $columns ?? [] ) ? $groupedBy : '';
        $this->groupByForm = $groupByForm
            ->setFormAction( '#' . $this->getID() )
            ->makeFields( $columns ?? [], $this->groupedBy )
            ->render();
        return $this;
    }

    /**
     * @param GoToIDEntityForm $goToIDForm
     * @return $this
     */
    public function setGoToIDForm(GoToIDEntityForm $goToIDForm): self
    {
        $this->goToIDForm = $goToIDForm->render();
        return $this;
    }

    /**
     * @param Entity $dummyEntity
     * @return $this
     */
    public function setDummyEntity(Entity $dummyEntity): self
    {
        $this->entity = $dummyEntity;
        return $this;
    }

    /**
     * @param Entity $entity
     * @return array
     */
    abstract public function extractEntityData(Entity $entity): array;

    /**
     * @param Entity $entity
     * @return string
     */
    public function getActionButton(Entity $entity): string
    {
        return $this->htmlUtility::getViewButton(
            $entity->getLink(),
            'View ' . ucfirst( $entity->entityName )
        );
    }

    /**
     * @return string
     */
    public function getRightAlignedHeaderHTML(): string
    {
        // $entity = $this->entity;
        if ( isset( $this->entity ) && $this->entity->canCreate() ) {
            $addNew = $this->htmlUtility::getButton( [
                'element' => 'a',
                'content' => 'Add New ' . ucwords( $this->entity->entityName ),
                'href' => $this->entity->getLink( false ),
                'class' => 'float-left btn btn-success ml-2'
            ] );
        }
        return $this->goToIDForm
            . ($addNew ?? '');
    }

    /**
     * @return array
     */
    protected function extractData(): array
    {
        foreach ( $this->entities->getAll() as $entity ) {
            $row = array_merge(
                [
                    'id' => $entity->id
                ],
                $this->extractEntityData( $entity ),
                [
                    'view' => $this->getActionButton( $entity ),
                    'errors' => $this->htmlUtility::getListGroup(
                        $entity->healthCheck()
                    )
                ]
            );
            $data[$entity->id] = $row;
        }

        if ( empty( $data ) ) {
            return [];
        }
        return $data ?? [];
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        if ( empty( $this->id ) ) {
            //count() is a hackish way to get a unique id, but sufficient for scroll-to-table
            $this->id = parent::getID() . '-' . $this->entities->getCount();
        }
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLeftAlignedHeaderHTML(): string
    {
        return $this->groupByForm ?? '';
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return  $this->entities->getCount() ;
    }

    /**
     * @return string
     */
    public function getTotalCountString(): string
    {
        return 'Total ' . ucfirst(
                 $this->entity->entityNamePlural
            );
    }

    /**
     * @return $this
     */
    public function removeErrors(): self
    {
        unset( $this->columns['errors'] );
        return $this;
    }
}