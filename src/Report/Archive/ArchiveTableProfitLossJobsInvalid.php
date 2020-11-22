<?php


namespace Phoenix\Report\Archive;


use Phoenix\Entity\JobOverPeriod;

/**
 * Class ArchiveTableJobs
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableProfitLossJobsInvalid extends ArchiveTableProfitLossJobsValid
{
    /**
     * @var string
     */
    protected string $title = 'Jobs Excluded From Report';

    /**
     * @param JobOverPeriod $job
     * @return string[]
     */
    public function extractMoreEntityData(JobOverPeriod $job): array
    {
        return [];
    }
}