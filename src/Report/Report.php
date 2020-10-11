<?php


namespace Phoenix\Report;

use Phoenix\Base;
use Phoenix\Format;
use Phoenix\Report\Archive\ArchiveTable;
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
     * @var bool
     */
    protected bool $fullwidth = true;

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
    protected bool $printButton = false;

    /**
     * @var string
     */
    private string $id;

    /**
     * Report constructor.
     *
     * @param HTMLTags $htmlUtility
     * @param Format   $format
     */
    public function __construct(HTMLTags $htmlUtility, Format $format)
    {
        $this->htmlUtility = $htmlUtility;
        $this->format = $format;
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
    public function includePrintButton(): self
    {
        $this->printButton = true;
        return $this;
    }

    /**
     * @return array
     */
    public function getNavLinks(): array
    {
        if ( $this->printButton ) {
            return [[
                'url' => '#',
                'text' => 'Print',
                'class' => 'bg-secondary print-button',
            ]];
        }
        return [];
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
    public function getAdditionalHeaderHTML(): string
    {
        return '';
    }

    /**
     * @return array
     */
    abstract public function extractData(): array;

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
        return $this->htmlUtility::getAlertHTML( $this->emptyMessage, $this->emptyMessageClass, false );
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
     * @param array $data
     * @return array
     */
    public function applyDefaults(array $data = []): array
    {
        $columns = $this->getColumns( 'default' );
        if ( empty( $columns ) ) {
            return $data;
        }
        foreach ( $data as $rowID => &$row ) {
            foreach ( $columns as $columnID => $default ) {
                $skipDefault = false;
                if ( !empty( $row[$columnID] ) ) {
                    $this->columns[$columnID]['not_empty'] = true;
                } elseif ( !isset( $row[$columnID] ) || ($row[$columnID] !== (float)0 && $row[$columnID] !== 0) ) {
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
        }
        return $data ?? [];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function renderReport(): string
    {
        $data = $this->extractData();
        $data = $this->applyDefaults( $data );
        $data = $this->format::formatColumnsValues( $data, $this->getColumns( 'format' ) );
        if ( empty( $data ) ) {
            return $this->getEmptyMessage();
        }

        return $this->htmlUtility::getTableHTML( [
            'data' => $data,
            'columns' => $this->getColumns(),
            'rows' => $this->getRowArgs(),
            'class' => $this->tableClass
        ] );
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function render(): string
    {
        ob_start(); ?>
        <div id="<?php echo $this->getID(); ?>" class="container mb-5<?php echo $this->printButton ? '' : ' d-print-none'; ?>">
            <?php echo $this->htmlUtility::getNavHTML( [
                'title' => $this->getTitle(),
                'nav_links' => $this->getNavLinks(),
                'heading_level' => 2,
                'html_right_aligned' => $this->getAdditionalHeaderHTML()
            ] ); ?>
            <div class="row">
                <div class="<?php echo $this->fullwidth ? 'col' : 'col-auto'; ?>">
                    <div class="grey-bg p-3">
                        <?php echo $this->renderReport(); ?>
                    </div>
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
        if ( empty( $property ) ) {
            return $this->columns;
        }
        foreach ( $this->columns as $columnName => $columnArgs ) {
            if ( is_string( $columnArgs ) && $property === 'title' ) {
                $return[$columnName] = $columnArgs;
                continue;
            }
            if ( isset( $columnArgs[$property] ) ) {
                $return[$columnName] = $columnArgs[$property];
            }
        }
        return $return ?? [];
    }
}