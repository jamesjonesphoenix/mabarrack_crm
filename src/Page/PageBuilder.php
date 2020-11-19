<?php

namespace Phoenix\Page;

use Phoenix\AbstractCRM;
use Phoenix\Entity\ShiftFactory;
use Phoenix\Format;
use Phoenix\Messages;
use Phoenix\PDOWrap;
use Phoenix\Report\ReportClient;
use Phoenix\Report\ReportFactory;
use Phoenix\Report\Shifts\ShiftsReportBuilder;
use Phoenix\URL;
use Phoenix\Utility\FormFields;

/**
 * @author James Jones
 * @property Format     $format
 * @property FormFields $HTMLUtility
 *
 * Class PageBuilder
 *
 * @package Phoenix\Page
 *
 */
abstract class PageBuilder extends AbstractCRM
{
    /**
     * @var FormFields
     */
    protected FormFields $_HTMLUtility;

    /**
     * @var Format
     */
    private Format $_format;

    /**
     * @var Page
     */
    protected Page $page;

    /**
     * @var URL
     */
    private URL $url;

    /**
     * @var ReportClient
     */
    private ReportClient $reportClient;

    /**
     * PageBuilder constructor.
     *
     * @param PDOWrap  $db
     * @param Messages $messages
     * @param URL      $url
     */
    public function __construct(PDOWrap $db, Messages $messages, URL $url)
    {
        $this->setURL( $url );
        parent::__construct( $db, $messages );

        $this->reportClient = new ReportClient(
            new ReportFactory(
                $this->HTMLUtility,
                $this->format,
                $this->getURL()
            ),
            $this->HTMLUtility,
            $this->db,
            $this->messages
        );
    }

    /**
     * @return URL
     */
    public function getURL(): URL
    {
        return clone $this->url;
    }

    /**
     * @param URL $url
     * @return $this
     */
    public function setURL(URL $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return Page
     */
    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * @return $this
     */
    abstract public function buildPage(): self;

    /**
     * @return Page
     */
    protected function getNewPage(): Page
    {
        return new Page( $this->HTMLUtility );
    }

    /**
     * @return FormFields
     */
    protected function HTMLUtility(): FormFields
    {
        if ( !empty( $this->_HTMLUtility ) ) {
            return $this->_HTMLUtility;
        }
        return $this->_HTMLUtility = new FormFields();
    }

    /**
     * @return Format
     */
    protected function format(): Format
    {
        if ( !empty( $this->_format ) ) {
            return $this->_format;
        }
        return $this->_format = new Format();
    }

    /**
     * @return ReportClient
     */
    public function getReportClient(): ReportClient
    {
        return $this->reportClient ;
    }
}