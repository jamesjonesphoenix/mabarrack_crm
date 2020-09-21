<?php

namespace Phoenix\Page;

use Phoenix\AbstractCRM;
use Phoenix\Format;
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
     * @return Page
     */
    public function getPage(): Page
    {
        return $this->page;
    }

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

}