<?php

/**
 * Class ph_Activities
 */
class ph_Activities
{
    /**
     * @var array
     */
    public $activities = array();

    /**
     * ph_Activities constructor.
     */
    public function __construct() {

    }

    /**
     * @return array
     */
    public function getActivities() {
        if ( count( $this->activities ) == 0 ) {
            $this->activities = get_rows( "activities" );
        }
        return (array)$this->activities;
    }

    /**
     * @param int $activity_ID
     * @return bool|mixed
     */
    private function getAttribute(int $activity_ID = 0, string $attribute = '' ) {
        $activities = $this->getActivities();
        if ( !empty( $activities[ $activity_ID ] ) )
            return $activity_str = $activities[ $activity_ID ][ $attribute ];
        return false;
    }

    /**
     * @param int $activity_ID
     * @return bool|mixed
     */
    public function getName($activity_ID = 0 ) {
        return $this->getAttribute($activity_ID, 'name');
    }

    /**
     * @param int $activity_ID
     * @return bool|mixed
     */
    public function getType($activity_ID = 0 ) {
        return $this->getAttribute($activity_ID, 'type');
    }

    /**
     * @param string $activity_name
     * @return mixed
     */
    public function getID($activity_name = 'Lunch' ) {
        $activities = $this->getActivities();
        //print_r( $activities );
        if ( !empty( $activity_name ) ) {
            foreach ( $activities as $activity ) {
                if ( $activity[ 'name' ] == $activity_name ) {
                    return $activity[ 'ID' ];
                }
            }
        }
    }
}