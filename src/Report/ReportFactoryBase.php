<?php


namespace Phoenix\Report;


use Phoenix\Format;
use Phoenix\URL;
use Phoenix\Utility\HTMLTags;

/**
 * Class ReportFactoryBase
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
abstract class ReportFactoryBase
{
    /**
     * @var HTMLTags
     */
    protected HTMLTags $htmlUtility;

    /**
     * @var Format
     */
    protected Format $format;

    /**
     * @var URL
     */
    protected URL $url;

    /**
     * Report Factory constructor.
     *
     * @param HTMLTags $htmlUtility
     * @param Format   $format
     * @param URL      $url
     */
    public function __construct(HTMLTags $htmlUtility, Format $format, URL $url)
    {
        $this->htmlUtility = $htmlUtility;
        $this->format = $format;
        $this->url = clone $url;

    }
}