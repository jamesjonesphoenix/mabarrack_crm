<?php


namespace Phoenix\Report;


use Phoenix\Base;
use Phoenix\Format;
use Phoenix\Messages;
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
     * @var Messages
     */
    protected Messages $messages;

    /**
     * @var HTMLTags
     */
    public HTMLTags $htmlUtility;

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * Report constructor.
     *
     * @param HTMLTags $htmlUtility
     * @param Format   $format
     * @param Messages $messages
     */
    public function __construct(HTMLTags $htmlUtility, Format $format, Messages $messages)
    {
        $this->htmlUtility = $htmlUtility;
        $this->format = $format;
        $this->messages = $messages;
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
     * @return string
     */
    abstract public function renderReport(): string;

    /**
     * @return string
     */
    public function render(): string{
        ob_start();
         ?>
        <div class="container mb-4">
            <?php echo $this->htmlUtility::getNavHTML( [
                'title' => $this->getTitle(),
                'nav_links' => $this->getNavLinks(),
                'heading_level' => 2,
                'html_right_aligned' => $this->getAdditionalHeaderHTML()
            ] ); ?>
            <div class="row">
                <div class="col">
                    <div class="grey-bg p-3">
                        <?php echo $this->renderReport(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }

}