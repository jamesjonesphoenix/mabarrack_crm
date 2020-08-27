<?php


namespace Phoenix\Page\WorkerChoose;

use Phoenix\Entity\CurrentUser;
use Phoenix\Page\PageBuilder;

/**
 * Class ChoosePageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page\WorkerChoose
 *
 */
abstract class ChoosePageBuilder extends PageBuilder
{
    /**
     * @var ChoosePage
     */
    protected ChoosePage $page;

    /**
     * @var CurrentUser
     */
    protected CurrentUser $user;

    /**
     * @var string
     */
    protected string $pageTitle = '';

    /**
     * @param CurrentUser $user
     * @return $this
     */
    public function addUser(CurrentUser $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return ChoosePage
     */
    public function getNewPage(): ChoosePage
    {
        return new ChoosePage( $this->HTMLUtility );
    }

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