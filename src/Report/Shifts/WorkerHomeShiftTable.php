<?php


namespace Phoenix\Report\Shifts;

use Phoenix\Entity\Shifts;

/**
 * Class WorkerHomeShiftTable
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class WorkerHomeShiftTable extends ShiftsReport
{
    /**
     *
     */
    protected string $title = 'Worker Shifts';

    /**
     * @var Shifts
     */
    protected Shifts  $shifts;

    /**
     * @var string
     */
    protected string $noShiftsMessage = 'No shifts.';

    /**
     * @return array
     */
    public function extractData(): array
    {
        foreach ( $this->shifts->getAll() as $shift ) {
            $shiftTableData[] = [
                'id' => $shift->id,
                'job' => $shift->job->id,
                'customer' => $shift->job->customer->name ?? '-',
                'description' => $shift->job->description,
                'date' => $shift->date,
                'time_started' => $shift->timeStarted ?? '-',
                'time_finished' => $shift->timeFinished ?? '-',
                'furniture' => $shift->getFurnitureString(),
                'activity' => $shift->activity->displayName ?? 'Unknown Activity',
            ];
        }
        return $shiftTableData ?? [];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function renderReport(): string
    {
        $shiftTableData = $this->extractData();
        if ( empty( $shiftTableData ) ) {
            return $this->htmlUtility::getAlertHTML( $this->noShiftsMessage, 'info' );
        }
        $shiftTableData = $this->format::formatColumnValues( $shiftTableData, 'date', 'date' );
        //annotate first date
        $firstKey = key($shiftTableData);
        $shiftTableData[$firstKey]['date'] = $this->format::annotateDate($shiftTableData[$firstKey]['date'] , true);


        $shiftTableData = $this->format::formatColumnValues( $shiftTableData, 'annotateDate', 'date' );

        return $this->htmlUtility::getTableHTML( [
            'data' => $shiftTableData,
            'columns' => [
                'id' => 'ID',
                'job' => 'Job',
                'customer' => 'Customer',
                'description' => 'Description',
                'date' => 'Date',
                'time_started' => 'Time Started',
                'time_finished' => 'Time Finished',
                'furniture' => 'Furniture',
                'activity' => 'Activity',
            ]
        ] );
    }
}