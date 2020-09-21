<?php


namespace Phoenix\Page\ReportPage;

use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\Shifts;
use Phoenix\Report\Shifts\ActivitySummary;

/**
 * Class ReportPageBuilderActivitySummary
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class ReportPageBuilderActivitySummary extends ReportPageBuilder
{
    /**
     * @return array
     */
    public function getShifts(): array
    {
        if ( !$this->validateDates() ) {
            return [];
        }
        return (new ShiftFactory( $this->db, $this->messages ))->getEntities( [
            'date' => [
                'value' => [
                    'start' => $this->dateStart,
                    'finish' => $this->dateFinish
                ],
                'operator' => 'BETWEEN'
            ]
        ], [
            'activity' => true,
            'worker' => ['shifts' => false]
        ] );
    }

    /**
     * @return $this
     */
    public function addReport(): self
    {
        $shifts = new Shifts( $this->getShifts() );
        $format = $this->format;
        $htmlUtility = $this->HTMLUtility;

        $this->page->addContent(
            (new ActivitySummary(
                $htmlUtility,
                $format
            ))->init( $shifts )->render() );
        return $this;
    }
}