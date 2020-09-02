<?php


namespace Phoenix\Report;


use Phoenix\Entity\Job;

/**
 * Class ChooseFurnitureTable
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class ChooseFurnitureTable extends Report
{
    /**
     *
     */
    protected string $title = 'Choose Job Furniture';

    /**
     * @var Job
     */
    protected Job $job;

    /**
     * @param Job|null $job
     * @return $this
     */
    public function init(Job $job = null): self
    {
        if ( $job !== null ) {
            $this->job = $job;
        }
        $this->setTitle( '<small>Job ID:</small> ' . $job->id );
        return $this;
    }

    /**
     * @return array
     */
    public function extractData(): array
    {
        $job = $this->job;
        $jobFurniture = $job->furniture ?? [];
        foreach ( $jobFurniture as $furniture ) {
            $furnitureTableData[] = [
                'name' => $furniture->name,
                'quantity' => $furniture->quantity,
                'select' => $this->htmlUtility::getButton( [
                    'element' => 'a',
                    'href' => 'worker.php?job=' . $job->id . '&furniture=' . $furniture->id . '&choose=activity',
                    'class' => 'btn btn-primary btn-lg',
                    'content' => 'Select'
                ] )
            ];
        }
        return $furnitureTableData ?? [];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function renderReport(): string
    {
        $furnitureTableData = $this->extractData();
        if ( empty( $furnitureTableData ) ) {
            return $this->htmlUtility::getAlertHTML( 'Job ' . $this->job->id . ' has no furniture to choose from.', 'info' );
        }
        return $this->htmlUtility::getTableHTML( [
            'data' => $furnitureTableData,
            'columns' => [
                'name' => 'Furniture',
                'quantity' => 'Quantity',
                'select' => ''
            ]
        ] );
    }
}