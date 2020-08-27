<?php


namespace Phoenix\Entity;

use Phoenix\Roles;

/**
 * @method CurrentUser|null getEntity(int $id = 0)
 *
 * Class CurrentUserFactory
 *
 * @author James Jones
 * @package Phoenix\Entity
 *
 */
class CurrentUserFactory extends UserFactory
{
    /**
     * @return CurrentUser
     */
    protected function instantiateEntityClass(): CurrentUser
    {
        return CurrentUser::instance( $this->db, $this->messages, new Roles() );
    }

    /**
     * @param int $pin
     * @return CurrentUser|null
     */
    public function getUserFromPin(int $pin = 0): ?CurrentUser
    {
        $users = $this->getEntities( ['pin' => $pin], ['shifts' => [
            'activity' => true,
            'furniture' => true,
            'job' => true,
            'customer' => true,
            'worker' => false //Don't waste CPU time provisioning shifts with worker - we already have the worker
        ]] );
        if ( count( $users ) > 1 ) {
            $this->addError( '<strong>Error:</strong> More than 1 user found with pin ' . $pin . '.' );
            return null;
        }
        if ( !($user = current( $users )) ) {
            return null;
        }
        //Add customers and furniture to user's shifts jobs to display on Worker dashboard
        foreach ( $user->shifts->getAll() as $shift ) {
            $jobs[$shift->job->id] = $shift->job;
        }
        $jobFactory = new JobFactory( $this->db, $this->messages );
        $jobs = $jobFactory->addOneToOneEntityProperties( $jobs ?? [], new CustomerFactory( $this->db, $this->messages ) );
        $jobs = $jobFactory->addFurnitureNames( $jobs );
        foreach ( $user->shifts->getAll() as $shift ) {
            $shift->job = $jobs[$shift->job->id];
        }
        return $user;
    }

}