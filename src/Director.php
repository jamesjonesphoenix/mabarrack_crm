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
     * @param array $inputArray
     * @return PageBuilder
     */
    abstract public function getPageBuilder(array $inputArray = []): PageBuilder;

    abstract public function doActions(array $inputArray = []): bool;
}