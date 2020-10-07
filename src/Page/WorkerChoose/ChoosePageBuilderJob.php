<?php

namespace Phoenix\Page\WorkerChoose;

use Phoenix\Entity\JobFactory;
use Phoenix\Report\ChooseJobTable;

/**
 * Class ChoosePageBuilderJob
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class ChoosePageBuilderJob extends ChoosePageBuilder
{
    /**
     * @return $this
     */
    public function addTitle(): self
    {
        $this->page->setTitle( 'Choose Job' );
        return $this;
    }

    /**
     * @return $this
     */
    public function addChooseTables(): self
    {
        $jobFactory = new JobFactory( $this->db, $this->messages );

        $recentJobs = $jobFactory->addFurnitureNames(
            $this->user->getLastWorkedJobs( 3 )
        );
        $factoryJob = $jobFactory->getJob( 0 );
        $activeJobs = $jobFactory->getActiveJobs();
        krsort( $activeJobs );
        foreach ( $activeJobs as $activeJob ) {
            $lastShift = $activeJob->getLastShift( $this->user->id );
            if ( $lastShift !== null ) {
                $activeJob->shifts = [$lastShift->id => $lastShift];
            }
        }

        $chooseJobsTable = (new ChooseJobTable(
            $this->HTMLUtility,
            $this->format
        ))
            ->setJobs( $recentJobs )
            ->setTitle( 'Most Recent Jobs' );
        /**
         * Recent Jobs
         */
        $this->page->addContent( $chooseJobsTable->render() );

        /**
         * Factory Job
         */
        if ( $factoryJob !== null ) {
            $lastShift = $factoryJob->getLastShift( $this->user->id );
            if ( $lastShift !== null ) {
                $factoryJob->shifts = [$lastShift->id => $lastShift];
            }
            $this->page
                ->addContent(
                    $chooseJobsTable
                        ->setJobs( [0 => $factoryJob] )
                        ->setTitle( 'Factory' )
                        ->render()
                );
        }
        /**
         * Active Jobs
         */
        $this->page
            ->addContent(
                $chooseJobsTable
                    ->setJobs( $activeJobs )
                    ->setTitle( 'All Active Jobs' )
                    ->render()
            );

        return $this;
    }
}