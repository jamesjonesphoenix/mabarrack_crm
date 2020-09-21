<?php

namespace Phoenix\Page\WorkerChoose;

use Phoenix\Entity\JobFactory;
use Phoenix\Report\ChooseFurnitureTable;

/**
 * Class ChoosePageBuilderFurniture
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class ChoosePageBuilderFurniture extends ChoosePageBuilder
{
    /**
     * @var int|null
     */
    private ?int $jobID;

    /**
     * @var string
     */
    protected string $pageTitle = 'Choose Furniture';

    /**
     * @param int|null $jobID
     * @return $this
     */
    public function setJobID(int $jobID = null): self
    {
        $this->jobID = $jobID;
        return $this;
    }

    /**
     * @return string[][]
     */
    public function getMenuItems(): array
    {
        return array_merge( [
            'choose_job' => [
                'url' => 'worker.php?choose=job',
                'text' => 'Choose Different Job',
                'class' => 'bg-info'
            ]],
            parent::getMenuItems()
        );
    }

    /**
     * @return $this
     */
    public function addChooseTables(): self
    {
        $jobID = $this->jobID ?? null;
        if ( $jobID === null ) {
            return $this;
        }
        $job = (new JobFactory( $this->db, $this->messages ))->getJob( $jobID );

        $this->page->addContent( (new ChooseFurnitureTable(
            $this->HTMLUtility,
            $this->format
        ))->init( $job )->render() );

        return $this;
    }
}