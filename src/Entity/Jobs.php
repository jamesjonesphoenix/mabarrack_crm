<?php


namespace Phoenix\Entity;

/**
 * @author James Jones
 * @property Job[] $entities
 * @method Job[] getAll()
 * @method Job getOne(int $id = null)
 *
 * Class Jobs
 *
 * @package Phoenix\Entity
 *
 */
class Jobs extends Entities
{
    /**
     * @return Jobs
     */
    public function getCompleteJobs(): Jobs
    {
        foreach ( $this->entities as $id => $job ) {
            if ( !empty( $job->healthCheck() ) || !empty( $job->completeCheck() ) ) {
                continue;
            }
            $jobs[$id] = $job;
        }
        return new self( $jobs ?? [] );
    }

    /**
     * @return Jobs
     */
    public function getIncompleteOrInvalidJobs(): Jobs
    {
        foreach ( $this->entities as $id => $job ) {
            if ( empty( $job->healthCheck() ) && empty( $job->completeCheck() ) ) {
                continue;
            }
            $jobs[$id] = $job;
        }
        return new self( $jobs ?? [] );
    }
}