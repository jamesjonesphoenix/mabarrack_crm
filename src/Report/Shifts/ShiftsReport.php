<?php


namespace Phoenix\Report\Shifts;

use Phoenix\Entity\Shifts;
use Phoenix\Report\Report;

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
     * @var string
     */
    protected string $noShiftsMessage = 'No shifts to report';

    /**
     * @param string $noShiftsMessage
     * @return $this
     */
    public function setNoShiftsMessage( string $noShiftsMessage = ''): self
    {
        $this->noShiftsMessage = $noShiftsMessage;
        return $this;
    }

    /**
     * @param Shifts $shifts
     * @return $this
     */
    public function init(Shifts $shifts): self
    {
        $this->shifts = $shifts;
        return $this;
    }
}