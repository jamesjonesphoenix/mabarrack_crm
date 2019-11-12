<?php


namespace Phoenix;


class AbstractCRM extends Base
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
}