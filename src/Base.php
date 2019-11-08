<?php

namespace Phoenix;

/***
 * Class Base
 */
class Base
{
    /**
     * @var Messages
     */
    protected $messages;

    /**
     * @var PDOWrap
     */
    protected $db;

    /**
     * Base constructor.
     *
     * @param PDOWrap|null $db
     * @param Messages|null $messages
     */
    public function __construct(PDOWrap $db = null, Messages $messages = null)
    {
        $this->db = $db;
        $this->messages = $messages;
    }

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
        $this->messages->add( '<strong>' . $name . '</strong> not found.' );
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