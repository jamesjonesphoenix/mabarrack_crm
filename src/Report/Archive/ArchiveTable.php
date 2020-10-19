<?php


namespace Phoenix\Report\Archive;

use Phoenix\Entity\Entity;
use Phoenix\Form\GoToIDEntityForm;
use Phoenix\Form\GroupByEntityForm;
use Phoenix\Format;
use Phoenix\Report\Report;
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
     * @var Entity[]
     */
    protected array $entities = [];

    /**
     * @var string
     */
    private string $groupedBy = '';

    /**
     * @var string
     */
    private string $groupByForm = '';

    /**
     * @var string
     */
    protected string $emptyMessage;

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
     * Report constructor.
     *
     * @param HTMLTags $htmlUtility
     * @param Format   $format
     */
    public function __construct(HTMLTags $htmlUtility, Format $format)
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

        parent::__construct( $htmlUtility, $format );

    }

    /**
     * @param Entity[]    $entities
     * @param Entity|null $entity Input this because the $entities array may be empty
     * @return $this
     */
    public function setEntities(array $entities = [], Entity $entity = null): self
    {
        $this->entities = $entities;
        if ( $entity !== null ) {
            $this->entity = $entity;
            $this->emptyMessage = 'No ' . $this->entity->entityNamePlural . ' found to report.';
        } elseif ( count( $entities ) > 0 ) {
            $this->entity = current( $entities );
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
        $entity = $this->entity;
        if ( $entity->canCreate() ) {
            $addNew = $this->htmlUtility::getButton( [
                'element' => 'a',
                'content' => 'Add New ' . ucwords( $entity->entityName ),
                'href' => $entity->getLink( false ),
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
        foreach ( $this->entities as $entity ) {
            $row = array_merge(
                [
                    'id' => $entity->id
                ],
                $this->extractEntityData( $entity ),
                [
                    'view' => $this->getActionButton( $entity ),
                    'errors' => $this->htmlUtility::getListGroup( $entity->healthCheck() )
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
            $this->id = parent::getID() . '-' . count( $this->entities );
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
        return count( $this->entities );
    }

    /**
     * @return string
     */
    public function getTotalCountString(): string
    {
        return 'Total ' . ucfirst( $this->entity->entityNamePlural );
    }

    /**
     * Renders 1 table if not grouped, multiple tables if grouped
     *
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function renderReport(array $data = []): string
    {
        $groupedBy = $this->groupedBy;
        foreach ( $data as $entityID => $row ) {
            $sortedData[$row[$groupedBy] ?? ''][$row['id']] = $row;
        }
        ksort( $sortedData );
        $tableColumns = $this->getValidTableColumns();
        $html = '';
        foreach ( $sortedData as $groupName => $dataset ) {
            $html .= $this->renderSingleArchive(
                $dataset,
                $tableColumns,
                $groupName
            );
        }
        return $html ?? '';
    }

    /**
     * @param array  $data
     * @param array  $columns
     * @param string $groupName
     * @return string
     * @throws \Exception
     */
    public function renderSingleArchive(array $data, array $columns = [], string $groupName = ''): string
    {
        ob_start();
        //&& !empty($columns[$this->groupedBy])
        if ( !empty( $this->groupedBy ) ) {
            $groupTitle = $columns[$this->groupedBy]['title'] ?? str_replace( '_', ' ', $this->groupedBy );
            ?>
            <h4 class="mx-3">
                <?php echo 'Group - ' . ucfirst( $groupTitle ) . ' ';
                $groupName = !empty( $groupName ) ? $groupName : 'N/A';
                echo $this->htmlUtility::getBadgeHTML( strip_tags( $groupName ) );
                ?>
            </h4>
        <?php } ?>

        <div class="grey-bg p-3 mb-5">
            <?php echo $this->htmlUtility::getTableHTML( [
                'data' => $data,
                'columns' => $columns,
                'class' => 'archive table-sorter',
            ] ); ?>
        </div>
        <?php return ob_get_clean();
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