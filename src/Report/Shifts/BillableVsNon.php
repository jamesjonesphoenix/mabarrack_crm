<?php


namespace Phoenix\Report\Shifts;


/**
 * Class BillableVsNon
 *
 * @author James Jones
 * @package Phoenix\Report\Shifts
 *
 */
class BillableVsNon extends ActivitySummary
{
    /**
     * @var string
     */
    protected string $title = 'Value Adding vs. Non-Chargeable Activities';

    /**
     * @return array
     */
    public function sortShifts(): array
    {
        foreach ( $this->shifts->getAll() as $shift ) {
            $chargeable =  $shift->activity->chargeable ? 'Value Adding' : 'Non Chargeable';
            $returnShifts[$chargeable][$shift->id] = $shift;
        }
        return $returnShifts ?? [];
    }
}