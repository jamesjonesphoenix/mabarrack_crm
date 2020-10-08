<?php

namespace Phoenix\Page\WorkerChoose;

use Phoenix\Entity\ActivityFactory;
use Phoenix\Entity\FurnitureFactory;
use Phoenix\Entity\Job;
use Phoenix\Entity\JobFactory;
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
    private ?int $furnitureID;

    private Job $job;

    /**
     * @param int|null $jobID
     * @return $this
     */
    public function setJob(int $jobID = null): self
    {
        if ( $jobID !== null ) {
            $job = (new JobFactory( $this->db, $this->messages ))->getEntity( $jobID );
            if ( $job !== null ) {
                $this->job = $job;
            }
        }
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
     * @return $this
     */
    public function addTitle(): self
    {
        $jobID = $this->job->id === 0 ? $this->HTMLUtility::getBadgeHTML( 'Factory' ) . ' Job' : 'Job' . $this->job->getIDBadge();
        if ( isset( $this->furnitureID ) ) {
            //$furniture = (new FurnitureFactory( $this->db, $this->messages ))->getEntity( $this->furnitureID );
            $furniture = $this->job->furniture[$this->furnitureID] ?? null;
            $furnitureString = $this->HTMLUtility::getBadgeHTML($furniture->name ?? 'Unknown Furniture') . ' in ';
        }
        $this->page->setTitle( 'Choose Activity for ' . ($furnitureString ?? '') . $jobID );
        return $this;
    }

    /**
     * @return string[][]
     */
    public function getMenuItems(): array
    {
        if ( count( $this->job->furniture ?? [] ) > 1 ) {
            $menuItems['choose_furniture'] = [
                'url' => 'worker.php?job=' . $this->job->id . '&choose=furniture',
                'text' => 'Choose Different Furniture',
                'class' => 'bg-info'
            ];
        }
        $menuItems['choose_job'] = [
            'url' => 'worker.php?choose=job',
            'text' => 'Choose Different Job',
            'class' => 'bg-info'
        ];
        return array_merge( $menuItems, parent::getMenuItems() );
    }

    /**
     * @return $this
     */
    public function addChooseTables(): self
    {
        $jobID = $this->job->id;
        $furnitureID = $this->furnitureID ?? null;
        if ( $jobID === null ) {
            return $this;
        }

        foreach ( (new ActivityFactory( $this->db, $this->messages ))->getEntities() as $activity ) {
            if ( $activity->name === 'Lunch' || $activity->category === 'Lunch' ) {
                continue;
            }
            if ( $jobID === 0 && !$activity->factoryOnly ) {
                continue;
            }
            if ( $jobID > 0 && $activity->factoryOnly ) {
                continue;
            }
            if ( !$activity->isActive() ) {
                continue;
            }
            // $nextPage = strtolower( $activity->name ) === 'other' ? 'other_comment' : 'next_shift';
            // $activityURLs[$activity->id] = 'worker.php?job=' . $jobID . '&activity=' . $activity->id . '&' . $nextPage . '=1';
            $comment = strtolower( $activity->name ) === 'other' ? '&other_comment=1' : '';
            $activityURLs[$activity->id] = 'worker.php?job=' . $jobID . '&activity=' . $activity->id . '&next_shift=1' . $comment;
            if ( $furnitureID !== null ) {
                $activityURLs[$activity->id] .= '&furniture=' . $furnitureID;
            }
            $sortedActivities[$activity->type][$activity->id] = $activity;
        }
        foreach ( $sortedActivities ?? [] as $activityType => $activities ) {
            $this->page->addContent( (new chooseActivityTable(
                $this->HTMLUtility,
                $this->format
            ))->setActivities(
                $activities,
                $activityURLs ?? [],
                $activityType
            )
                ->setTitle( $activityType . ' Activities' )
                ->render()
            );
        }


        return $this;
    }
}