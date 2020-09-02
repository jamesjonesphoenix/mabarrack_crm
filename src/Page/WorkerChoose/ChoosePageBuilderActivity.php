<?php

namespace Phoenix\Page\WorkerChoose;

use Phoenix\Entity\ActivityFactory;
use Phoenix\Report\ChooseActivityTable;

/**
 * Class ChoosePageBuilderFurniture
 *
 * @author James Jones
 * @package Phoenix\Page
 */
class ChoosePageBuilderActivity extends ChoosePageBuilder
{
    /**
     * @var int|null
     */
    private ?int $jobID;

    /**
     * @var int|null
     */
    private ?int $furnitureID;

    /**
     * @var string
     */
    protected string $pageTitle = 'Choose Activity';

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
     * @param int|null $furnitureID
     * @return $this
     */
    public function setFurnitureID(int $furnitureID = null): self
    {
        $this->furnitureID = $furnitureID;
        return $this;
    }

    /**
     * @return string[][]
     */
    public function getMenuItems(): array
    {
        return array_merge( [
            'choose_furniture' => [
                'url' => 'worker.php?job=' . $this->jobID . '&choose=furniture',
                'text' => 'Choose Different Furniture',
                'class' => 'bg-info'
            ],
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
        $furnitureID = $this->furnitureID ?? null;
        if ( $jobID === null ) {
            return $this;
        }
        foreach ( (new ActivityFactory( $this->db, $this->messages ))->getEntities() as $activity ) {
            if ( $activity->name === 'Lunch' || $activity->category === 'Lunch' ) {
                continue;
            }
            if ( (empty( $jobID ) || $jobID === 0) && !$activity->factoryOnly ) {
                continue;
            }
            if ( $jobID > 0 && $activity->factoryOnly ) {
                continue;
            }
            if ( !$activity->isActive() ) {
                continue;
            }
            $nextPage = strtolower( $activity->name ) === 'other' ? 'other-comment' : 'next_shift';
            $activityURLs[$activity->id] = 'worker.php?job=' . $jobID . '&activity=' . $activity->id . '&furniture=' . $furnitureID . '&' . $nextPage . '=1';
            $activities[$activity->id] = $activity;
        }

        $this->page->addChooseTable( (new chooseActivityTable(
            $this->HTMLUtility,
            $this->format
        ))->init(
            $activities ?? [],
            $activityURLs ?? []
        ) );

        return $this;
    }
}