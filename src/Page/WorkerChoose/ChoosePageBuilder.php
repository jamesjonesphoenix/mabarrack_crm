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
     * @return $this
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage()
            ->setNavLinks( $this->getMenuItems() );
        $this->addTitle();
        return $this->addChooseTables();
    }

    /**
     * @return $this
     */
    abstract public function addTitle(): self;

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