<?php


namespace Phoenix\Report;

use Phoenix\Base;
use Phoenix\Format;
use Phoenix\URL;
use Phoenix\Utility\HTMLTags;

/**
 * @author James Jones
 *
 * Class Report
 *
 * @package Phoenix\Report
 *
 */
abstract class Report extends Base
{
    /**
     * @var Format
     */
    protected Format $format;

    /**
     * @var HTMLTags
     */
    public HTMLTags $htmlUtility;

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @var array
     */
    protected array $columns = [];

    /**
     * @var array
     */
    protected array $rowArgs = [];

    /**
     * @var string
     */
    protected string $emptyMessage = 'Nothing to report.';

    /**
     * @var string
     */
    protected string $emptyMessageClass = 'warning';

    /**
     * @var string
     */
    protected string $tableClass = '';

    /**
     * @var bool
     */
    protected bool $includePrintButton = false;

    /**
     * @var bool
     */
    protected bool $collapseButton = true;

    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string
     */
    protected string $fullRowName = 'row';

    /**
     * @var bool
     */
    protected bool $fullWidth = true;

    /**
     * @var bool
     */
    protected bool $includeColumnToggles = false;

    /**
     * @var array|null
     */
    protected ?array $data;

    /**
     * @var string
     */
    protected string $groupedBy = '';

    /**
     * @var URL
     */
    private URL $url;

    /**
     * @var bool
     */
    protected bool $allowGroupBy = true;

    /**
     * @var int
     */
    protected int $countMinimum = 5;

    /**
     * @var array
     */
    protected array $navLinks;

    /**
     * Report constructor.
     *
     * @param HTMLTags $htmlUtility
     * @param Format   $format
     * @param URL      $url
     */
    public function __construct(HTMLTags $htmlUtility, Format $format, URL $url)
    {
        $this->htmlUtility = $htmlUtility;
        $this->format = $format;
        $this->url = $url;
    }

    /**
     * @return URL
     */
    public function getURL(): URL
    {
        return clone $this->url;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title = ''): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return $this
     */
    public function enablePrintButton(): self
    {
        $this->includePrintButton = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disablePrintButton(): self
    {
        $this->includePrintButton = false;
        return $this;
    }

    /**
     * @return $this
     */
    public function enableCollapseButton(): self
    {
        $this->collapseButton = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableCollapseButton(): self
    {
        $this->collapseButton = false;
        return $this;
    }

    /**
     * @return array
     */
    public function buildNavLinks(): array
    {
        if ( $this->collapseButton ) {
            $collapsibleID = $this->getID() . '-report';
            $all = !empty( $this->groupedBy ) ? ' All' : '';
            $return['minimise'] = [
                'href' => '#' . $collapsibleID,
                'content' => 'Minimise' . $all,
                'class' => 'btn-danger minimise-button',
                'role' => 'button',
                'data' => [
                    'toggle' => 'collapse',
                ]
            ];
            $return['expand'] = [
                'href' => '#' . $collapsibleID,
                'content' => 'Expand' . $all,
                'class' => 'btn-success expand-button',
                'role' => 'button',
                'data' => [
                    'toggle' => 'collapse',
                ]
            ];
        }
        if ( $this->includePrintButton ) {
            $return['print_button'] = [
                'href' => '#',
                'content' => 'Print',
                'class' => 'bg-secondary print-button',
            ];
        }
        return $return ?? [];
    }

    /**
     * @return array
     */
    public function getNavLinks(): array
    {
        return $this->navLinks ?? ($this->navLinks = $this->buildNavLinks());
    }

    /**
     * @param string $linkName
     * @param array  $navLink
     * @return $this
     */
    public function addNavLink(string $linkName = '', array $navLink = []): self
    {
        $navLinks = $this->getNavLinks();
        foreach ( $navLink as $argName => $arg ) {
            $navLinks[$linkName][$argName] = $arg;
        }
        $this->navLinks = $navLinks;
        return $this;
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        if ( empty( $this->id ) ) {
            //count() is a hackish way to get a unique id, but sufficient for scroll-to-table
            $this->id = strtolower( substr( strrchr( get_class( $this ), '\\' ), 1 ) );
        }
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRightAlignedHeaderHTML(): string
    {
        return '';
    }

    /**
     * @return array
     */
    abstract protected function extractData(): array;

    /**
     * @return array
     */
    public function getRowArgs(): array
    {
        return $this->rowArgs;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setEmptyMessage(string $message = ''): self
    {
        $this->emptyMessage = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmptyMessage(): string
    {
        return '<div class="grey-bg px-3 py-2 mb-5">'
            . $this->htmlUtility::getAlertHTML( $this->emptyMessage, $this->emptyMessageClass, false )
            . '</div>';
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setEmptyMessageClass(string $class = ''): self
    {
        if ( !empty( $class ) ) {
            $this->emptyMessageClass = $class;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getValidTableColumns(): array
    {
        foreach ( $this->getColumns() as $columnID => $columnArgs ) {
            if ( empty( $columnArgs['remove_if_empty'] ) || empty( $columnArgs['is_empty'] ) ) {
                $tableColumns[$columnID] = $columnArgs;
            }
        }
        return $tableColumns ?? [];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function tagRemovableColumns(array $data = []): self
    {
        if ( !$this->includeColumnToggles ) {
            return $this;
        }
        $columnsRemovable = $this->getColumns( 'remove_if_empty' );
        foreach ( $columnsRemovable as $columnID => $value ) {
            $hasData = false;
            foreach ( $data as $rowID => $row ) {
                if ( !empty( $row[$columnID] ) ) {
                    // $this->columns[$columnID]['not_empty'] = true;
                    $hasData = true;
                    break;
                    //unset( $columnsRemovable[$columnID] );
                }
            }
            if ( !$hasData ) {
                $this->columns[$columnID]['is_empty'] = true;
                // unset( $this->columns[$columnID] );
                // $this->columns[$columnID] = null;
            }
        }
        return $this;
    }

    /**
     * @param array $data
     * @return array
     */
    public function applyDefaults(array $data = []): array
    {
        $columnsDefaults = $this->getColumns( 'default' );
        if ( empty( $columnsDefaults ) ) {
            return $data;
        }
        foreach ( $data as $rowID => &$row ) {
            foreach ( $columnsDefaults as $columnID => $default ) {
                if ( !empty( $row[$columnID] )
                    || (isset( $row[$columnID] ) && is_numeric( $row[$columnID] )) ) {
                    continue;
                }

                $skipDefault = false;
                $columnSubIDs = explode( '.', $columnID );
                array_pop( $columnSubIDs );
                if ( !empty( $columnSubIDs ) ) {
                    $combinedColumnSubID = '';
                    foreach ( $columnSubIDs as $columnSubID ) {
                        $combinedColumnSubID .= $columnSubID;
                        if ( !empty( $row[$combinedColumnSubID] ) ) {
                            $skipDefault = true;
                        }
                        $combinedColumnSubID .= '.';
                    }
                }
                if ( !$skipDefault ) {
                    $row[$columnID] = $default;
                }

            }
        }
        return $data ?? [];
    }

    /**
     * @param array $data
     * @return array
     */
    public function addFullRowNameToData(array $data = []): array
    {
        foreach ( $data as $rowID => $row ) {
            foreach ( $row as $columnID => $item ) {
                $newColumnID = $columnID !== $this->fullRowName ? $this->fullRowName . '.' . $columnID : $columnID;
                $return[$rowID][$newColumnID] = $item;
            }
        }
        return $return ?? [];
    }

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     */
    /*
    public function renderReport(array $data = []): string
    {
        return '<div class="grey-bg p-3">' . $this->htmlUtility::getTableHTML( [
                'data' => $data,
                'columns' => $this->getValidTableColumns(),
                'rows' => $this->getRowArgs(),
                'class' => $this->tableClass
            ] ) . '</div>';
    }
    */

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
        foreach ( $data as $rowID => $row ) {
            $sortedData[$row[$groupedBy] ?? ''][$rowID] = $row;
        }
        ksort( $sortedData );
        if ( !empty( $sortedData['totals'] ) ) {
            $sortedData = ['totals' => $sortedData['totals']] + $sortedData;
        }
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
        $groupName = strip_tags( $groupName );
        $id = $this->getID()
            . (!empty( $groupName ) ? '-' . str_replace( ' ', '-', strtolower( $groupName ) ) : '')
            . '-report';
        ob_start();
        if ( !empty( $this->groupedBy ) ) { ?>
            <div class="row align-items-center mb-2">
                <div class="col mx-3">
                    <?php
                    $groupTitle = $columns[$this->groupedBy]['title'] ?? str_replace( '_', ' ', $this->groupedBy ); ?>
                    <h4 class="mb-0">
                        <?php echo 'Group - ' . ucfirst( $groupTitle ) . ' ';
                        $groupName = !empty( $groupName ) ? ucfirst( $groupName ) : 'N/A';
                        echo $this->htmlUtility::getBadgeHTML( $groupName );
                        ?>
                    </h4>
                </div>
                <div class="col-auto mx-3">
                    <?php
                    echo $this->htmlUtility::getButton( [
                            'href' => '#' . $id,
                            'content' => 'Minimise',
                            'class' => 'btn text-white btn-danger minimise-button',
                            'role' => 'button',
                            'data' => [
                                'toggle' => 'collapse',
                            ]
                        ] )
                        . $this->htmlUtility::getButton( [
                            'href' => '#' . $id,
                            'content' => 'Expand',
                            'class' => 'btn text-white btn-success expand-button',
                            'role' => 'button',
                            'data' => [
                                'toggle' => 'collapse',
                            ]
                        ] );
                    ?>
                </div>
            </div>
        <?php } ?>
        <div class="grey-bg p-3 mb-5">
            <div <?php echo !empty( $this->groupedBy ) ? 'id="' . $id . '"' : ''; ?> class="show">
                <?php echo $this->htmlUtility::getTableHTML( [
                    'data' => $data,
                    'columns' => $columns,
                    'class' => $this->tableClass,
                    'rows' => $this->getRowArgs(),
                ] ); ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @return string
     */
    public function getLeftAlignedHeaderHTML(): string
    {
        return '';
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getTotalCountString(): string
    {
        return 'Total Items';
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processData(array $data = []): array
    {
        $this->tagRemovableColumns( $data );
        // $data = $this->addFullRowNameToData( $data );
        $data = $this->applyDefaults( $data );
        foreach ( $this->getColumns( 'hidden' ) as $columnID => $value ) {
            $this->columns[$columnID]['class'] = !empty( $this->columns[$columnID]['class'] ) ? $this->columns[$columnID]['class'] . ' d-none' : 'd-none';
        }
        return $this->format::formatColumnsValues( $data, $this->getColumns( 'format' ) );
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data ?? ($this->data = $this->processData(
                $this->extractData()
            ));
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function render(): string
    {
        $data = $this->getData();
        $printNone = $this->includePrintButton ? '' : ' d-print-none';
        if ( empty( $data ) ) {
            $this->disableCollapseButton();
        }

        //

        ob_start(); ?>
        <div id="<?php echo $this->getID(); ?>" class="container <?php echo $printNone; ?>">
            <?php echo $this->htmlUtility::getNavHTML( [
                'title' => $this->getTitle(),
                'nav_links' => $this->getNavLinks(),
                'heading_level' => 2,
                'html_left_aligned' => $this->getLeftAlignedHeaderHTML(),
                'html_right_aligned' => $this->getRightAlignedHeaderHTML()
            ] ); ?>
        </div>
        <div class="container d-print-none">
            <div class="row align-items-center mx-0">
                <?php if ( !empty( $data ) ) {
                    echo $this->renderColumnToggles();
                    $totalCount = $this->getTotalCount();
                    if ( $totalCount > $this->countMinimum ) { ?>
                        <div class="col-auto mb-3"><h5 class="mb-0 entity-count"><?php echo $this->getTotalCountString()
                                . ' ' . $this->htmlUtility::getBadgeHTML( $totalCount ); ?></h5>
                        </div><?php
                    }
                } ?>
            </div>
        </div>

        <div class="report-container container<?php echo $this->fullWidth ? '-fluid' : ''; ?> position-relative mb-3<?php echo $printNone; ?>">
            <div class="row show<?php echo $this->fullWidth ? ' justify-content-center' : ''; ?>" id="<?php echo $this->getID(); ?>-report">
                <div class="report-table-column col-auto d-flex flex-column align-items-stretch">
                    <?php echo empty( $data ) ? $this->getEmptyMessage() : $this->renderReport( $data ); ?>
                </div>
            </div>
            <div class="row<?php echo $this->fullWidth ? ' justify-content-center' : ''; ?>">
                <div class="report-table-column col-auto d-flex flex-column align-items-stretch<?php echo $this->fullWidth ? '' : ' w-100'; ?>">
                    <div class="grey-bg p-3 mb-5"></div>
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
        if ( !$this->includeColumnToggles ) {
            return '';
        }
        ob_start(); ?>
        <div class="col mb-3">
            <div class="row align-items-center no-gutters">
                <div class="col">
                    <form class="form-inline mb-n2">
                        <?php $i = 0;
                        foreach ( $this->getColumns() as $columnID => $columnArgs ) {

                            $title = is_string( $columnArgs ) ? $columnArgs : ($columnArgs['toggle_label'] ?? $columnArgs['title']);

                            if ( empty( $title ) || empty( $columnID ) || (!empty( $columnArgs['remove_if_empty'] ) && !empty( $columnArgs['is_empty'] )) ) {
                                $i++;
                                continue;
                            }
                            $id = uniqid( 'toggle-' . ucfirst( $columnID ) . '-', true ); ?>
                            <div class="custom-control custom-checkbox mr-3 mb-2">
                                <input class="custom-control-input column-toggle" type="checkbox" value="<?php echo $columnID; ?>" data-column="<?php echo $i; ?>"
                                       id="<?php echo $id; ?>" <?php echo empty( $columnArgs['hidden'] ) ? 'checked' : ''; ?>>
                                <label class="custom-control-label"
                                       for="<?php echo $id; ?>"><?php echo $title; ?></label>
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
     * @param string $property
     * @return array
     */
    public function getColumns(string $property = ''): array
    {
        /*
        $fullRowName = $this->fullRowName;
        foreach ( $this->columns as $columnID => $columnArgs ) {
            // $columns[$fullRowName . '.' . $columnID] = $columnArgs;
            $columns[$columnID] = $columnArgs;
        }
        */

        if ( empty( $property ) ) {
            return $this->columns ?? [];
        }

        foreach ( $this->columns ?? [] as $columnID => $columnArgs ) {

            if ( is_string( $columnArgs ) && $property === 'title' ) {
                $return[$columnID] = $columnArgs;
                continue;
            }
            if ( isset( $columnArgs[$property] ) ) {
                $return[$columnID] = $columnArgs[$property];
            }
        }
        return $return ?? [];
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
     * @return $this
     */
    public function disableColumnToggles(): self
    {
        $this->includeColumnToggles = false;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableGroupBy(): self
    {
        $this->allowGroupBy = false;
        $this->groupedBy = '';
        return $this;
    }

    /**
     * @param array|string $columnIDs
     * @param array        $args
     * @return $this
     */
    public function editColumn($columnIDs = [], $args = []): self
    {
        if ( is_string( $columnIDs ) ) {
            $columnIDs = [$columnIDs];
        }
        foreach ( $columnIDs as $columnID ) {
            foreach ( $args as $key => $value ) {
                $this->columns[$columnID][$key] = $value;
            }
        }
        return $this;
    }
}