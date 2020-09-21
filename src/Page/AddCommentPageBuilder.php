<?php


namespace Phoenix\Page;

use Phoenix\Entity\ShiftFactory;
use Phoenix\Form\AddCommentForm;

/**
 * Class ChoosePageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page\WorkerChoose
 *
 */
class AddCommentPageBuilder extends WorkerPageBuilder
{
    /**
     * @var int
     */
    private int $shiftID;

    /**
     * @param int|null $shiftID
     * @return $this
     */
    public function setShiftID(int $shiftID = null): self
    {
        if ( $shiftID !== null ) {
            $this->shiftID = $shiftID;
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function buildPage(): self
    {
        $title = 'Add comment';
        if ( isset( $this->shiftID ) ) {
            $comment = (new ShiftFactory( $this->db, $this->messages ))->getEntity( $this->shiftID )->activityComments;
            if(!empty($comment)){
                $title = 'Update comment';
            }
            $title .=  ' for shift ID: ' . $this->shiftID;
        }
        $this->page = $this->getNewPage()
            ->setTitle( $title . '?' )
            ->setNavLinks( $this->getMenuItems() )
            ->addContent(
                (new AddCommentForm( $this->HTMLUtility, $this->shiftID, $comment ?? '' ))->makeFields()->render()
            );
        return $this;
    }

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