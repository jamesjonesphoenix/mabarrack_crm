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
    private bool $includeColumnToggles = true;

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
     * @param string $columnID
     * @param array  $args
     * @return $this
     */
    public function editColumn($columnID = '', $args = []): self
    {
        if ( empty( $this->columns[$columnID] ) ) {
            return $this;
        }
        foreach ( $args as $key => $value ) {
            $this->columns[$columnID][$key] = $value;
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function hideInessentialColumns(): self
    {
        $inessentialColumns = $this->getColumns( 'inessential' );
        foreach ( $inessentialColumns as $columnID => $inessential ) {
            if ( !empty( $inessential ) ) {
                $this->columns[$columnID]['hidden'] = true;
            }
        }
        return $this;
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
    public function getAdditionalHeaderHTML(): string
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
    public function extractData(): array
    {
        $columns = $this->getColumns();
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
            foreach ( $columns as $columnID => $columnArgs ) {
                if ( !empty( $row[$columnID] ) ) {
                    $this->columns[$columnID]['not_empty'] = true;
                } elseif ( !isset( $row[$columnID] ) || ($row[$columnID] !== (float)0 && $row[$columnID] !== 0) ) {
                    $row[$columnID] = $this->columns[$columnID]['default'] ?? '';
                }
            }
            $data[$entity->id] = $row;
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
     * @throws \Exception
     */
    public function render(): string
    {
        $archivesHTML = $this->renderReport();
        $printNone = $this->printButton ? '' : ' d-print-none';

        ob_start(); ?>
        <div class="container d-print-none" id="<?php echo $this->getID(); ?>">
            <?php echo $this->htmlUtility::getNavHTML( [
                'title' => $this->getTitle(),
                'nav_links' => $this->getNavLinks(),
                'heading_level' => 2,
                'html_left_aligned' => $this->groupByForm,
                'html_right_aligned' => $this->getAdditionalHeaderHTML()
            ] ); ?>
        </div>
        <?php
        if ( !empty( $archivesHTML ) && ($this->includeColumnToggles || count( $this->entities ) > 5) ) { ?>
            <div class="container mb-3 d-print-none">
                <div class="row align-items-center mx-0">
                    <?php if ( $this->includeColumnToggles ) {
                        echo $this->renderColumnToggles();
                    } ?>
                    <?php if ( count( $this->entities ) > 5 ) { ?>
                        <div class="col-auto"><h5 class="mb-0 entity-count">Total <?php echo ucfirst( $this->entity->entityNamePlural )
                                    . ' ' . $this->htmlUtility::getBadgeHTML( count( $this->entities ) ); ?></h5></div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
        <div class="container-fluid position-relative<?php echo $printNone; ?>">
            <div class="row justify-content-center">
                <div class="archive-table-column col-auto d-flex flex-column align-items-stretch">
                    <?php echo empty( $archivesHTML ) ? '<div class="grey-bg px-3 py-2 mb-5">' . $this->getEmptyMessage() . '</div>' : $archivesHTML; ?>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @return string
     */
    public function renderColumnToggles(): string
    {
        ob_start(); ?>
        <div class="col">
            <div class="row align-items-center no-gutters">
                <?php // <div class="col-auto mr-2"><h5 class="mb-0">Toggle Columns</h5></div>
                ?>
                <div class="col">
                    <form class="form-inline mb-n2">
                        <?php $i = 0;
                        foreach ( $this->getColumns() as $columnName => $columnArgs ) {
                            if ( empty( $columnArgs['title'] ) ) {
                                $i++;
                                continue;
                            }
                            $id = uniqid( 'toggle-' . ucfirst( $columnName ) . '-', true ); ?>
                            <div class="custom-control custom-checkbox mr-3 mb-2">
                                <input class="custom-control-input column-toggle" type="checkbox" value="<?php echo $columnName; ?>" data-column="<?php echo $i; ?>"
                                       id="<?php echo $id; ?>" <?php echo empty( $columnArgs['hidden'] ) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="<?php echo $id; ?>"><?php echo $columnArgs['title']; ?></label>
                            </div>
                            <?php $i++;
                        } ?>
                    </form>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * Renders 1 table if not grouped, multiple tables if grouped
     *
     * @return string
     * @throws \Exception
     */
    public function renderReport(): string
    {
        $groupedBy = $this->groupedBy;
        foreach ( $this->extractData() as $entityID => $data ) {
            $sortedData[$data[$groupedBy] ?? ''][$data['id']] = $data;
        }

        if ( empty( $sortedData ) ) {
            return '';
        }
        ksort( $sortedData );
        foreach ( $this->getColumns() as $columnID => $columnArgs ) {
            if ( !empty( $columnArgs['remove_if_empty'] ) && empty( $columnArgs['not_empty'] ) ) {
                continue;
            }
            $tableColumns[$columnID] = $columnArgs;
            if ( !empty( $columnArgs['hidden'] ) ) {
                $tableColumns[$columnID]['class'] = !empty( $columnArgs['class'] ) ? $columnArgs['class'] . 'd-none' : 'd-none';
            }
        }
        $this->columns = $tableColumns ?? [];
        $html = '';
        foreach ( $sortedData as $groupName => $dataset ) {
            $html .= $this->renderSingleArchive(
                $this->format::formatColumnsValues( $dataset, $this->getColumns( 'format' ) ),
                $tableColumns ?? [],
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
            <h4 class=" mx-3">
                <?php echo 'Group - ' . ucfirst( $groupTitle ) . ' ';
                $groupName = !empty( $groupName ) ? $groupName : 'N/A';
                if ( strpos( $groupName, '<a' ) !== false ) {
                    echo str_replace( 'class="', 'class="badge badge-primary ', $groupName );
                } else {
                    echo $this->htmlUtility::getBadgeHTML( $groupName );
                } ?>
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

    /**
     * @return $this
     */
    public function dontIncludeColumnToggles(): self
    {
        $this->includeColumnToggles = false;
        return $this;
    }


}