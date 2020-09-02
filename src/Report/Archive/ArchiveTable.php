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
    private bool $thereIsAnError = false;

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
                    'view' => $this->htmlUtility::getViewButton( $entity->getLink(), 'View ' . ucfirst( $entity->entityName ) ),
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
        if ( !empty( $archivesHTML ) ) { ?>
            <div class="container">
                <div class="row align-items-center mt-3 mx-0">
                    <div class="col">
                        <div class="row align-items-center no-gutters">
                            <div class="col-auto mr-2"><h5 class="mb-0">Toggle Columns</h5></div>
                            <div class="col">
                                <form class="form-inline mx-n2 mb-n2">
                                    <?php $i = 0;
                                    foreach ( $this->getColumns() as $columnName => $columnArgs ) {
                                        if ( empty( $columnArgs['title'] ) ) {
                                            $i++;
                                            continue;
                                        }
                                        $id = uniqid( 'toggle-' . ucfirst( $columnName ) . '-', true ); ?>
                                        <div class="custom-control custom-checkbox mx-2 mb-2">
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
                    <?php if ( count( $this->entities ) > 5 ) { ?>
                        <div class="col-auto"><h5 class="mb-0 entity-count">Total <?php echo ucfirst( $this->entity->entityNamePlural ); ?> <span
                                        class="badge badge-primary"><?php echo count( $this->entities ); ?></span></h5></div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
        <div class="container-fluid mb-4 position-relative mt-3">
            <div class="row justify-content-center">
                <div class="archive-table-column col-auto d-flex flex-column align-items-stretch">
                    <?php echo empty( $archivesHTML ) ? '<div class="grey-bg px-3 py-2">' . $this->getEmptyReportMessage() . '</div>' : $archivesHTML; ?>
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
                $this->thereIsAnError = true;
            }
        }
        if ( empty( $sortedData ) ) {
            return '';
        }
        ksort( $sortedData );
        $columns = $this->getColumns( 'title' );
        if ( empty( $this->thereIsAnError ) ) {
            unset( $columns['errors'] );
        }
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
        $hiddenColumns = $this->getColumns( 'hidden' );
        foreach ( $hiddenColumns as $columnName => $hiddenColumn ) {
            $columnsClasses[$columnName] = 'd-none';
        }
        $groupedBy = $this->groupedBy;
        ?>
        <div class="grey-bg p-3 mb-5">
            <?php if ( !empty( $groupedBy ) ) { ?>
                <h4><small><?php echo ucfirst( $columns[$this->groupedBy] ) . ' - '; ?></small><?php echo !empty( $groupName ) ? $groupName : 'N/A'; ?></h4>
            <?php } ?>
            <?php echo $this->htmlUtility::getTableHTML( [
                'data' => $data,
                'columns' => $columns,
                'rowsClasses' => [],
                'columnsClasses' => $columnsClasses ?? [],
                'subheaders' => [],
                'class' => 'archive table-sorter',
            ] ); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param string $property
     * @return array
     */
    public
    function getColumns(string $property = ''): array
    {
        $columns = array_merge(
            ['id' => [
                'title' => 'ID'
            ]],
            $this->columns
        );
        if ( !empty( $this->thereIsAnError ) ) {
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