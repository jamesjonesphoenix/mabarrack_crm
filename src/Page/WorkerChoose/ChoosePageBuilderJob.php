<?php

namespace Phoenix\Page\WorkerChoose;

use Phoenix\Entity\JobFactory;

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
     * @throws \Exception
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

        $chooseJobsTable = $this->getReportClient()->getFactory()->getChooseJobTable()
            ->setJobs( $recentJobs )
            ->setTitle( 'Most Recent Jobs' )
            ->setEmptyMessage( 'No recently worked jobs to choose from.' );

        $recentJobsHTML = $chooseJobsTable->render();
        $activeJobsHTML = $chooseJobsTable
            ->setJobs( $activeJobs )
            ->setTitle( 'All Active Jobs' )
            ->setEmptyMessageClass( 'info' )
            ->setEmptyMessage( 'No active jobs to choose from.' )
            ->render();
        if ( $factoryJob !== null ) {
            $lastShift = $factoryJob->getLastShift( $this->user->id );
            if ( $lastShift !== null ) {
                $factoryJob->shifts = [$lastShift->id => $lastShift];
            }
            $factoryJobsHTML = $chooseJobsTable
                ->setJobs( [0 => $factoryJob] )
                ->setTitle( 'Factory' )
                ->setEmptyMessageClass( 'danger' )
                ->setEmptyMessage( 'Factory job missing.' )
                ->render();
        }
        $this->page->addContent(
            $recentJobsHTML . ($factoryJobsHTML ?? '') . $activeJobsHTML
        );


        return $this;
    }
}