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
        $messages = $this->messages;
        $jobFactory = new JobFactory( $this->db, $this->messages );

        $recentJobs = $this->user->getLastWorkedJobs( 3 );
        $recentJobsTables = (new ChooseJobTable(
            $htmlUtility,
            $format,
            $messages
        ))->init( $recentJobs );
        $recentJobsTables->setTitle( 'Most Recent Jobs' );
        $this->page->addChooseTable( $recentJobsTables );

        $factoryJob = $jobFactory->getJob( 0 );
        if ( $factoryJob !== null ) {
            $lastShift = $factoryJob->getLastShift( $this->user->id );
            if ( $lastShift !== null ) {
                $factoryJob->shifts = [$lastShift->id => $lastShift];
            }
            if ( $factoryJob !== null ) {
                $factoryJob->furniture = 'N/A';
            }
            $factoryJobTable = (new ChooseJobTable(
                $htmlUtility,
                $format,
                $this->messages
            ))->init( [0 => $factoryJob] );
            $factoryJobTable->setTitle( 'Factory Work' );

            $this->page->addChooseTable( $factoryJobTable );
        }


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
            $this->messages
        ))->init( $activeJobs );
        $activeJobsTable->setTitle( 'All Active Jobs' );
        $this->page->addChooseTable( $activeJobsTable );

        return $this;
    }
}