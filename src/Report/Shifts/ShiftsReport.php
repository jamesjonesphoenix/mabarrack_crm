<?php


namespace Phoenix\Report\Shifts;


use Phoenix\Entity\Shifts;
use Phoenix\Format;
use Phoenix\Report\Report;
use Phoenix\URL;
use Phoenix\Utility\HTMLTags;

/**
 * Class ShiftsReport
 *
 * @author James Jones
 * @package Phoenix\Report\Shifts
 *
 */
abstract class ShiftsReport extends Report
{
    /**
     * @var Shifts
     */
    protected Shifts $shifts;

    /**
     * Report constructor.
     *
     * @param HTMLTags $htmlUtility
     * @param Format   $format
     * @param URL      $url
     */
    public function __construct(HTMLTags $htmlUtility, Format $format, URL $url)
    {
        parent::__construct( $htmlUtility, $format, $url );
        $this->setEntities( new Shifts() );
    }

    /**
     * @param Shifts $shifts
     * @return $this
     */
    public function setEntities(Shifts $shifts): self
    {
        $this->shifts = $shifts;
        return $this;
    }
}