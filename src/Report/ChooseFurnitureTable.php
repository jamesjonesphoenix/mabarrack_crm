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
     * @var Job
     */
    protected Job $job;

    /**
     * @var string
     */
    protected string $emptyMessageClass = 'info';

    /**
     * @var array
     */
    protected array $columns = [
        'name' => 'Furniture',
        'quantity' => 'Quantity',
        'select' => ''
    ];

    /**
     * @param Job|null $job
     * @return $this
     */
    public function setJobs(Job $job = null): self
    {
        if ( $job !== null ) {
            $this->job = $job;
        }
        $this->emptyMessage = 'Job ' . $this->job->getIDBadge() . ' has no furniture to choose from.';
        return $this;
    }

    /**
     * @return array
     */
    protected function extractData(): array
    {
        $jobFurniture = $this->job->furniture ?? [];
        foreach ( $jobFurniture as $furniture ) {
            $furnitureTableData[] = [
                'name' => $furniture->name,
                'quantity' => $furniture->quantity,
                'select' => $this->htmlUtility::getButton( [
                    'element' => 'a',
                    'href' => 'worker.php?job=' . $this->job->id . '&furniture=' . $furniture->id . '&choose=activity',
                    'class' => 'btn btn-primary btn-lg',
                    'content' => 'Select'
                ] )
            ];
        }
        return $furnitureTableData ?? [];
    }
}