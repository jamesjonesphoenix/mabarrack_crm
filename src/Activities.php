<?php

namespace Phoenix;

/**
 * Class Activities
 */
class Activities extends Base
{
    /**
     * @var array
     */
    public $activities = [];

    /**
     * Gets all activities
     *
     * @return array
     */
    public function getActivities(): array
    {
        if ( count( $this->activities ) === 0 ) {
            $this->activities = $this->db->getRows( 'activities' );
        }
        return (array)$this->activities;
    }

    public function getSortedActivities(): array
    {

    }

    /**
     * @param int|null $activityID
     * @param string|null $attribute
     * @return bool|mixed
     */
    private function getAttribute(int $activityID = null, string $attribute = null)
    {
        $activities = $this->getActivities();
        if ( !empty( $activities[$activityID] ) ) {
            return $activities[$activityID][$attribute];
        }
        return null;
    }

    /**
     * @param int $activityID
     * @return bool|mixed
     */
    public function getName(int $activityID = 0)
    {
        return $this->getAttribute( $activityID, 'name' );


    }

    /**
     * @param int $activityID
     * @return bool|mixed|string
     */
    public function getDisplayName(int $activityID = 0)
    {
        $name = $this->getName( $activityID );

        $activityTypes = [];
        foreach ( $this->getActivities() as $activity ) {
            if ( $activity['name'] === $name && !in_array( $activity['type'], $activityTypes, true ) ) {
                $activityTypes[] = $activity['type'];
            }
        }
        if ( count( $activityTypes ) > 1 ) {
            $name = $this->getType( $activityID ) . ' ' . $name;
        }
        return $name;
    }

    /**
     * @param int $activityID
     * @return bool|mixed
     */
    public function getType(int $activityID = 0)
    {
        return $this->getAttribute( $activityID, 'type' );
    }

    /**
     * @param int $activityID
     * @return bool
     */
    public function isActive(int $activityID = 0): bool
    {
        $deactivated = $this->getActivities()[$activityID]['deactivated'];
        if ( !empty( $deactivated ) ) {
            return false;
        }
        return true;
    }

    /**
     * @param int $activityID
     * @return bool
     */
    public function factoryOnly(int $activityID = 0): bool
    {
        $factoryOnly = $this->getActivities()[$activityID]['factoryOnly'];
        if ( !empty( $factoryOnly ) ) {
            return true;
        }
        return false;
    }

    /**
     * @param string $activity_name
     * @return mixed
     */
    public function getID(string $activity_name = 'Lunch')
    {
        $activities = $this->getActivities();
        //print_r( $activities );
        if ( !empty( $activity_name ) ) {
            foreach ( $activities as $activity ) {
                if ( $activity['name'] === $activity_name ) {
                    return $activity['ID'];
                }
            }

        }
        return false;
    }
}