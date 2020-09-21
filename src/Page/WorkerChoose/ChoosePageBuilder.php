<?php


namespace Phoenix\Page\WorkerChoose;

use Phoenix\Page\Page;
use Phoenix\Page\WorkerPageBuilder;

/**
 * Class ChoosePageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page\WorkerChoose
 *
 */
abstract class ChoosePageBuilder extends WorkerPageBuilder
{
    /**
     * @var Page
     */
    protected Page $page;


    /**
     * @var string
     */
    protected string $pageTitle = '';


    /**
     * @return $this
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage()
            ->setTitle( $this->pageTitle )
            ->setNavLinks( $this->getMenuItems() );
        return $this->addChooseTables();
    }

    /**
     * @return $this
     */
    abstract public function addChooseTables(): self;

    /**
     * @return \string[][]
     */
    public function getMenuItems(): array
    {
        return [
            'cancel' => [
                'url' => 'worker.php',
                'text' => 'Cancel',
                'class' => 'bg-secondary'
            ]
        ];
    }
}