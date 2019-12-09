<?php

namespace Phoenix;

/**
 * Class UserFactory
 */
class UserFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected $className = 'User';

    /**
     * @var string
     */
    protected $tableName = 'users';

    /**
     * Alias for getEntities()
     *
     * @param int $id
     * @return User
     */
    public function getUser(int $id = 0): User
    {
        return $this->getEntities( ['ID' => $id], true )[$id];
    }

    /**
     * Alias for getEntities()
     *
     * @param array $queryArgs
     * @param bool $provision
     * @return User[]
     */
    public function getUsers(array $queryArgs = [], $provision = false): array
    {
        return $this->getEntities( $queryArgs, $provision );
    }

    /**
     * @param array $queryArgs
     * @param bool $provision
     * @return User[]
     */
    public function getEntities(array $queryArgs = [], $provision = false): array
    {
        $users = $this->getClassesFromDBWrapper( $queryArgs );
        if ( !$provision || empty( $users ) ) {
            return $users;
        }

        //Add shifts for each worker to User
        $shiftFactory = new ShiftFactory($this->db,$this->messages);
        $users = $this->addManyToOneEntityProperties( $users, $shiftFactory,'worker' );
        return $users;
    }


    /**
     * @return User
     */
    protected function instantiateEntityClass(): Entity
    {
        return new User( $this->db, $this->messages );
    }

    /**
     * @param array $queryArgs
     * @return User[]
     */
    protected function getClassesFromDBWrapper(array $queryArgs = []): array
    {
        return $this->instantiateEntitiesFromDB( $queryArgs );
    }

    /**
     * Gets staff only. Only need staff with current implementations
     *
     * @param User[] $entities
     * @param string $propertyName
     * @return array
     */
    protected function getEntityIDs(array $entities = [], string $propertyName = 'id'): array
    {
        foreach ( $entities as $entity ) {
            //worker aligns with user. Need to change this if we start provisioning admin User classes
            if ( $entity->role === 'staff' ) {
                $entityIDs[$entity->$propertyName] = $entity->$propertyName;
            }
        }
        return $entityIDs ?? [];
    }
}