<?php

namespace Phoenix;

/**
 *
 * @method Activity[] getAll(): array
 *
 * Class ActivityFactory
 */
class ActivityFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected $className = 'Activity';

    /**
     * @var string
     */
    protected $tableName = 'activities';

    /**
     * Alias for getEntities()
     *
     * @param int $id
     * @return Activity
     */
    public function getActivity(int $id = 0): Activity
    {
        return $this->getEntities( ['ID' => $id], true )[$id];
    }

    /**
     * Alias for getEntities()
     *
     * @param array $queryArgs
     * @param bool $provision
     * @return Activity[]
     */
    public function getActivities(array $queryArgs = [], $provision = false): array
    {
        return $this->getEntities( $queryArgs, $provision );
    }

    /**
     * @param array $queryArgs
     * @param array $provision
     * @return Activity[]
     */
    public function getEntities(array $queryArgs = [], $provision = []): array
    {
        $activities = $this->getClassesFromDBWrapper( $queryArgs );
        if ( !$provision || empty( $activities ) ) {
            return $activities;
        }
        foreach ( $activities as &$activity ) {
            $activity->displayName;
        }
        return $activities;
    }

    /**
     * @return Activity
     */
    protected function instantiateEntityClass(): Entity
    {
        return new Activity( $this->db, $this->messages );
    }

    /**
     * @param array $queryArgs
     * @return Activity[]
     */
    protected function getClassesFromDBWrapper(array $queryArgs = []): array
    {
        return $this->instantiateEntitiesFromDB( $queryArgs );
    }
}