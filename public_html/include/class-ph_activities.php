<?php

class ph_Activities
{
    public $activities = array();

    public function __construct() {

    }

    public function get_activities() {
        if ( count( $this->activities ) == 0 ) {
            $this->activities = get_rows( "activities" );
        }
        return (array)$this->activities;
    }

    public function get_activity_name( $activity_ID = 0 ) {
        $activities = $this->get_activities();
        if ( !empty( $activities[ $activity_ID ] ) )
            return $activity_str = $activities[ $activity_ID ][ 'name' ];
        return false;
    }

    public function get_activity_id( $activity_name = 'Lunch' ) {
        $activities = $this->get_activities();
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