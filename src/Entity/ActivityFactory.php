<?php

namespace Phoenix\Entity;

/**
 * @method Activity getEntity(int $id = 0)
 * @method Activity[] instantiateEntitiesFromDB(array $queryArgs = [])
 *
 * Class ActivityFactory
 */
class ActivityFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected string $entityName = 'activity';

    /**
     * @var string
     */
    protected string $entityNamePlural = 'activities';

    /**
     * @return Activity
     */
    protected function instantiateEntityClass(): Activity
    {
        return new Activity( $this->db, $this->messages );
    }

    /**
     * Queries DB for all entities.
     * Returns entities as array to make a <select> form field.
     * Used by Phoenix/EntityForm->getOptionDropdownFieldHTML()
     *
     * @return array [<option> value1 => <option> name1, <option> value2 => <option> name2, ...]
     */
    public function getOptionsArray(): array
    {
        $activities = $this->getAll();
        $activitiesOptions = array_column( $activities, 'displayName', 'id' );
        asort( $activitiesOptions );
        return $activitiesOptions;
    }

    /**
     * @param array $queryArgs
     * @param bool  $provision
     * @return Activity[]
     */
    public function getEntities(array $queryArgs = [], $provision = false): array
    {
        return parent::getEntities( $queryArgs, true );
    }

    /**
     * @param Activity[] $activities
     * @param false      $provision
     * @return Activity[]
     */
    public function provisionEntities(array $activities = [], $provision = false): array
    {
        $activityNames = [];
        foreach ( $activities as $activity ) {
            if ( empty( $activityNames[$activity->name] ) ) {
                $activityNames[$activity->name] = 0;
            }
            $activityNames[$activity->name]++;
        }
        foreach ( $activities as &$activity ) {
            if ( $activity->type !== 'General' ) {
                $activity->displayName = $activity->type . ' ' . $activity->name;
            } elseif ( $activityNames[$activity->name] > 1 ) {
                $activity->displayName = 'Unspecific ' . $activity->name;
            }
        }

        //if ( !$provision || empty( $activities ) ) { return $activities; }
        return $activities;
    }
}