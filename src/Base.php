<?php

namespace Phoenix;

/***
 * Class Base
 */
class Base
{
    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if ( method_exists( $this, $name ) ) {
            $this->$name( $value );
        } else {
            // Getter/Setter not defined so set as property of object
            $this->$name = $value;
        }
    }

    public function __isset($name)
    {
        return false;
    }

    /**
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        if ( method_exists( $this, $name ) ) {
            return $this->$name();
        }

        if ( property_exists( $this, $name ) ) {
            // Getter/Setter not defined so return property if it exists
            return $this->$name;
        }
        return null;
    }
}