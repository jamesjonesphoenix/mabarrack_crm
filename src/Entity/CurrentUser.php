<?php

namespace Phoenix\Entity;


use Phoenix\Messages;
use Phoenix\PDOWrap;
use Phoenix\Roles;

/**
 * Wraps User class in singleton so we only have one logged in user
 *
 * Class CurrentUser
 */
class CurrentUser extends User
{
    /**
     * @var CurrentUser|null
     */
    protected static ?CurrentUser $_instance = null;

    /**
     * Singletons should not be cloneable.
     */
    protected function __clone() { }

    /**
     * Singletons should not be restorable from strings.
     */
    public function __wakeup()
    {
        throw new \RuntimeException( 'Cannot unserialise a singleton.' );
    }

    /**
     * @param PDOWrap|null  $db
     * @param Messages|null $messages
     * @param Roles|null    $roles
     * @return CurrentUser
     */
    public static function instance(PDOWrap $db = null, Messages $messages = null, Roles $roles = null): CurrentUser
    {
        if ( self::$_instance === null ) {
            self::$_instance = new self( $db, $messages , $roles);
        }
        return self::$_instance;
    }

    /**
     * The Singleton's constructor should always be private to prevent direct
     * construction calls with the `new` operator.
     *
     * @param PDOWrap|null  $db
     * @param Messages|null $messages
     * @param Roles|null    $roles
     */
    protected function __construct(PDOWrap $db = null, Messages $messages = null, Roles $roles = null) {
        parent::__construct($db,$messages, $roles);
    }

    /**
     * @return string
     */
    public function secondOrThirdPerson(): string
    {
        return 'you';
    }
}