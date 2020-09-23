<?php


namespace Phoenix\Report\Archive;

use Phoenix\Entity\Entity;
use Phoenix\Form\GoToIDEntityForm;
use Phoenix\Form\GroupByEntityForm;
use Phoenix\Report\Report;

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
     * @var array
     */
    protected array $columns = [];

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
    private string $emptyReportMessage;

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
    private bool $hideErrors = true;

    /**
     * @var bool|null
     */
    private ?bool $includeErrors = null;

    /**
     * @var bool
     */
    private bool $includeColumnToggles = true;

    /**
     * @param false $errorEntitiesOnly
     * @return $this
     */
    public function hideInessentialColumns($errorEntitiesOnly = false): self
    {
        if ( $errorEntitiesOnly ) {
            $this->hideErrors = false;
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
     * @return array
     */
    public function extractData(): array
    {
        foreach ( $this->entities as $entity ) {
            $data[$entity->id] = array_merge(
                [
                    'id' => $entity->id
                ],
                $this->extractEntityData( $entity ),
                [
                    'view' => $this->getActionButton( $entity ),
                    'errors' => $entity->healthCheck()
                ]
            );
        }
        return $data ?? [];
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
     * @return string
     */
    public function getEmptyReportMessage(): string
    {
        return $this->emptyReportMessage ?? $this->htmlUtility::getAlertHTML( 'No ' . $this->entity->entityNamePlural . ' found to report.', 'warning', false );
    }

    /**
     * @param string $message
     * @param string $type
     * @return $this
     */
    public function setEmptyReportMessage(string $message = '', string $type = 'warning'): self
    {
        $this->emptyReportMessage = $this->htmlUtility::getAlertHTML( $message, $type, false );
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function render(): string
    {
        $archivesHTML = $this->renderReport();

        ob_start(); ?>
        <div class="container" id="archive-table">
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
            <div class="container mb-3">
                <div class="row align-items-center mx-0">
                    <?php if ( $this->includeColumnToggles ) {
                        echo self::renderColumnToggles( $this->getColumns() );
                    } ?>
                    <?php if ( count( $this->entities ) > 5 ) { ?>
                        <div class="col-auto"><h5 class="mb-0 entity-count">Total <?php echo ucfirst( $this->entity->entityNamePlural ); ?> <span
                                        class="badge badge-primary"><?php echo count( $this->entities ); ?></span></h5></div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
        <div class="container-fluid position-relative">
            <div class="row justify-content-center">
                <div class="archive-table-column col-auto d-flex flex-column align-items-stretch">
                    <?php echo empty( $archivesHTML ) ? '<div class="grey-bg px-3 py-2 mb-4">' . $this->getEmptyReportMessage() . '</div>' : $archivesHTML; ?>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @param array $columns
     * @return string
     */
    public static function renderColumnToggles(array $columns = []): string
    {
        ob_start(); ?>
        <div class="col">
            <div class="row align-items-center no-gutters">
                <?php // <div class="col-auto mr-2"><h5 class="mb-0">Toggle Columns</h5></div> ?>
                <div class="col">
                    <form class="form-inline mb-n2">
                        <?php $i = 0;
                        foreach ($columns as $columnName => $columnArgs ) {
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
            if ( !empty( $data['errors'] ) ) {
                $thereIsAnError = true;
            }
        }
        if ( $this->includeErrors === null && !empty( $thereIsAnError ) ) {
            $this->includeErrors = true;
        }

        if ( empty( $sortedData ) ) {
            return '';
        }
        ksort( $sortedData );

        $columns = $this->getColumns();
        foreach ( $columns as $columnID => &$columnArgs ) {
            if ( !empty( $columnArgs['hidden'] ) ) {
                $columnArgs['class'] = !empty( $columnArgs['class'] ) ? $columnArgs['class'] . 'd-none' : 'd-none';
            }
        }
        unset( $columnArgs );

        $html = '';
        foreach ( $sortedData as $groupName => $dataset ) {
            $html .= $this->renderSingleArchive(
                $this->format::formatColumnsValues( $dataset, $this->getColumns( 'format' ) ),
                $columns,
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
        if ( !empty( $this->groupedBy ) ) { ?>
            <h4 class=" mx-3">
                <?php echo 'Group - ' . ucfirst( $columns[$this->groupedBy]['title'] ) . ' ';
                $groupName = !empty( $groupName ) ? $groupName : 'N/A';
                if ( strpos( $groupName, '<a' ) !== false ) {
                    echo str_replace( 'class="', 'class="badge badge-primary ', $groupName );
                } else { ?>
                    <span class="badge badge-primary"><?php echo $groupName; ?></span>
                <?php } ?>
            </h4>
        <?php } ?>

        <div class="grey-bg p-3 mb-4">
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
    public function ignoreErrors(): self
    {
        $this->includeErrors = false;
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

    /**
     * @param string $property
     * @return array
     */
    public function getColumns(string $property = ''): array
    {
        $id = array_merge( [
            'title' => 'ID'
        ], $this->columns['id'] ?? []);
        $columns = array_merge(
            ['id' => []],
            $this->columns
        );
        $columns['id'] = $id;

        if ( !empty( $this->includeErrors ) ) {
            $columns['errors'] = [
                'title' => 'Errors',
                'hidden' => $this->hideErrors
            ];
        }
        $columns['view'] = ['title' => ''];

        if ( empty( $property ) ) {
            return $columns;
        }
        foreach ( $columns as $columnName => $column ) {
            if ( isset( $column[$property] ) ) {
                $return[$columnName] = $column[$property];
            }
        }
        return $return ?? [];
    }
}