<?php

namespace Phoenix;

/**
 * Wraps User class in singleton so we only have one logged in user
 *
 * Class CurrentUser
 */
class CurrentUser extends User
{
    /**
     * @var null
     */
    protected static $_instance;

    /**
     * @param PDOWrap|null $db
     * @param Messages|null $messages
     * @param string $value
     * @param string $field
     * @return CurrentUser|null
     */
    public static function instance(PDOWrap $db = null, Messages $messages = null, string $value = '', string $field = 'pin'): ?CurrentUser
    {
        if ( self::$_instance === null ) {
            self::$_instance = new self( $db, $messages, $value, $field );
        }
        return self::$_instance;
    }

}