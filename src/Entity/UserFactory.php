<?php

namespace Phoenix\Entity;

use Phoenix\Roles;

/**
 * @method User|null getEntity(int $id = 0)
 * @method User[] getEntities(array $queryArgs = [], $provision = false)
 *
 * Class UserFactory
 */
class UserFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected string $entityName = 'user';

    /**
     * @return User
     */
    protected function instantiateEntityClass(): User
    {
        return new User( $this->db, $this->messages, new Roles() );
    }

    /**
     * @param User[] $users
     * @param false  $provision
     * @return Shift[]
     */
    public function provisionEntities(array $users = [], $provision = false): array
    {
        if ( !$this->canProvision( $provision, 'shifts' ) ) {
            return $users;
        }
        if ( $provision === true || $provision['shifts'] === true ) {
            $provisionShifts = [
                'activity' => true,
                'furniture' => true,
                'job' => ['customer' => true],
                'worker' => false //Don't waste CPU time provisioning shifts with worker - we already have the worker as the parent entity
            ];
        } else {
            $provisionShifts = $provision['shifts'];
        }
        $provisionShifts['worker'] = false;

        $shiftFactory = new ShiftFactory( $this->db, $this->messages );
        $users = $this->addManyToOneEntityProperties( $users, $shiftFactory, $provisionShifts, 'worker' );

        return $users;
    }
}