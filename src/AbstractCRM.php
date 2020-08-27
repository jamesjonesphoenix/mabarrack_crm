<?php


namespace Phoenix;


/**
 * Class AbstractCRM
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class AbstractCRM extends Base
{
    /**
     * @var ?Messages
     */
    protected ?Messages $messages;

    /**
     * @var ?PDOWrap
     */
    protected ?PDOWrap $db;

    /**
     * Base constructor.
     *
     * @param PDOWrap|null  $db
     * @param Messages|null $messages
     */
    public function __construct(PDOWrap $db = null, Messages $messages = null)
    {
        $this->db = $db;
        $this->messages = $messages;
    }

    /**
     * @param string $errorText
     * @return bool
     */
    public function addError(string $errorText = ''): bool
    {
        if ( $this->messages !== null ) {
            $this->messages->add( $errorText, 'danger' );
        } else {
            echo 'Messaging class not added';
        }
        return false;


    }
}