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
    protected string $title = 'Billable vs. Non-Billable Activities';

    /**
     * @return array
     */
    public function sortShifts(): array
    {
        foreach ( $this->shifts->getAll() as $shift ) {
            $chargeable =  $shift->activity->chargeable ? 'Billable' : 'Non Billable';
            $returnShifts[$chargeable][$shift->id] = $shift;
        }
        return $returnShifts ?? [];
    }
}