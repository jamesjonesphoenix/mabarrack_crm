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

    /**
     * @return \string[][]
     */
    public function getNavLinks(): array
    {
        return [
            [
                'url' => '#',
                'text' => 'Activities Summary'
            ]
        ];
    }

}