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
     * @var string
     */
    protected string $pageTitle = 'Choose Job';

    /**
     * @return $this
     */
    public function addChooseTables(): self
    {
        $format = $this->format;
        $htmlUtility = $this->HTMLUtility;
        $jobFactory = new JobFactory( $this->db, $this->messages );
        /**
         * Recent Jobs
         */
        $recentJobs = $jobFactory->addFurnitureNames(
            $this->user->getLastWorkedJobs( 3 )
        );
        $recentJobsTables = (new ChooseJobTable(
            $htmlUtility,
            $format
        ))->init( $recentJobs );
        $recentJobsTables->setTitle( 'Most Recent Jobs' );
        $this->page->addContent( $recentJobsTables->render() );
        /**
         * Factory Job
         */
        $factoryJob = $jobFactory->getJob( 0 );
        if ( $factoryJob !== null ) {
            $lastShift = $factoryJob->getLastShift( $this->user->id );
            if ( $lastShift !== null ) {
                $factoryJob->shifts = [$lastShift->id => $lastShift];
            }
            $factoryJobTable = (new ChooseJobTable(
                $htmlUtility,
                $format,
            ))->init( [0 => $factoryJob] );
            $factoryJobTable->setTitle( 'Factory' );
            $this->page->addContent( $factoryJobTable->render() );
        }
        /**
         * Active Jobs
         */
        $activeJobs = $jobFactory->getActiveJobs();
        krsort( $activeJobs );
        foreach ( $activeJobs as $activeJob ) {
            $lastShift = $activeJob->getLastShift( $this->user->id );
            if ( $lastShift !== null ) {
                $activeJob->shifts = [$lastShift->id => $lastShift];
            }
        }

        $activeJobsTable = (new ChooseJobTable(
            $htmlUtility,
            $format,
        ))->init( $activeJobs );
        $activeJobsTable->setTitle( 'All Active Jobs' );
        $this->page->addContent( $activeJobsTable->render() );

        return $this;
    }
}