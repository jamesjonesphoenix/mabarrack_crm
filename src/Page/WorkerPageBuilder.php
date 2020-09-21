<?php


namespace Phoenix\Page;


use Phoenix\Entity\CurrentUser;
use Phoenix\Entity\User;

/**
 * Class WorkerPageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class WorkerPageBuilder extends PageBuilder
{
    /**
     * @var User
     */
    protected User $user;

    /**
     * @param CurrentUser $user
     * @return $this
     */
    public function setUser(CurrentUser $user): self
    {
        $this->user = $user;
        return $this;
    }
}