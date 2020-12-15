<?php

namespace Phoenix\Entity;

use Phoenix\Roles;

/**
 * @method User|null getEntity(int $id = 0, $provision = true)
 * @method User[] getEntities(array $queryArgs = [], $provision = false)
 * @method User provisionEntity(User $entity, $provision = false)
 * @method User[] addManyToOneEntityProperties(array $entities = [], EntityFactory $additionFactory = null, $provisionArgs = false, $joinPropertyName = '')
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
     * @param User[]     $users
     * @param bool|array $provision
     * @return User[]
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
                'employee' => false //Don't waste CPU time provisioning shifts with worker - we already have the worker as the parent entity
            ];
        } else {
            $provisionShifts = $provision['shifts'];
        }
        $provisionShifts['employee'] = false;

        $users = $this->addManyToOneEntityProperties(
            $users,
            new ShiftFactory( $this->db, $this->messages ),
            $provisionShifts,
            'employee'
        );
/*
        foreach($users as $user){
            foreach($user->shifts->getAll() as $shiftID => $shift){
                $shift->worker = $user;
                //$shifts[$shiftID] =
            }
        }
*/
        return $users;
    }


    /**
     * @return array
     */
    public function getOptionsArray(): array
    {
        $options = array_column(
            $this->getAll(),
            'name',
            'id'
        );
        asort($options);
        return $options;
    }
}