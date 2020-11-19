<?php


namespace Phoenix;


use Phoenix\Page\PageBuilder;

/**
 * Class Director
 *
 * @author James Jones
 * @package Phoenix
 *
 */
abstract class Director extends AbstractCRM
{
    /**
     * @var URL
     */
    protected URL $url;

    /**
     * Base constructor.
     *
     * @param PDOWrap|null     $db
     * @param Messages|null    $messages
     * @param URL              $url
     */
    public function __construct(PDOWrap $db, Messages $messages, URL $url)
    {
        parent::__construct( $db, $messages );
        $this->url = $url;
    }

    /**
     * @param array $inputArray
     * @return PageBuilder
     */
    abstract public function getPageBuilder(array $inputArray = []): PageBuilder;

    /**
     * @param array $inputArray
     * @return bool
     */
    abstract public function doActions(array $inputArray = []): bool;

}