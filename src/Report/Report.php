<?php


namespace Phoenix\Report;

use Phoenix\Base;
use Phoenix\Format;
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
     * @return array
     */
    public function getNavLinks(): array
    {
        return [];
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
     * @return string
     * @throws \Exception
     */
    public function renderReport(): string
    {
        $data = $this->extractData();
        if ( empty( $data ) ) {
            return $this->htmlUtility::getAlertHTML( 'No jobs to report.', 'warning' );
        }
        return $this->htmlUtility::getTableHTML( [
            'data' => $data,
            'columns' => $this->getColumns(),
            'rows' => $this->getRowArgs()
        ] );
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function render(): string
    {
        ob_start();
        ?>
        <div class="container mb-4">
            <?php echo $this->htmlUtility::getNavHTML( [
                'title' => $this->getTitle(),
                'nav_links' => $this->getNavLinks(),
                'heading_level' => 2,
                'html_right_aligned' => $this->getAdditionalHeaderHTML()
            ] );


            ?>
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