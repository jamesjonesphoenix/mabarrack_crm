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
     * @return $this
     */
    public function getCompleteJobs(): self
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
     * @return $this
     */
    public function getIncompleteOrInvalidJobs(): self
    {
        foreach ( $this->entities as $id => $job ) {
            if ( empty( $job->healthCheck() ) && empty( $job->completeCheck() ) ) {
                continue;
            }
            $jobs[$id] = $job;
        }
        return new self( $jobs ?? [] );
    }

    /**
     * @param string $type
     * @param bool   $exclusive
     * @return $this
     */
    public function getJobsOfType(string $type = '', $exclusive = false): self
    {

        if ( $exclusive ) {
            foreach ( $this->entities as $jobID => $job ) {
                $mayBeLegit = false;
                $wrongActivityType = false;
                foreach ( $job->shifts->getAll() as $shiftID => $shift ) {
                    if ( $shift->activity->type !== $type && $shift->activity->type !== 'General' ) {
                        $wrongActivityType = true;
                        break;
                    }
                    if ( $shift->activity->type === $type ) {
                        $mayBeLegit = true;

                    }
                }
                if ( $mayBeLegit && !$wrongActivityType ) {
                    $jobs[$jobID] = $job;
                }
            }
            return new static( $jobs ?? [] );
        }

        foreach ( $this->entities as $jobID => $job ) {
            foreach ( $job->shifts->getAll() as $shiftID => $shift ) {
                if ( $shift->activity->type === $type ) {
                    $jobs[$jobID] = $job;
                    break;
                }
            }
        }

        return new static( $jobs ?? [] );
    }

}