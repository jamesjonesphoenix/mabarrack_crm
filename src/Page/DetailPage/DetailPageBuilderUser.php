<?php


namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\User;
use Phoenix\Entity\UserFactory;
use Phoenix\Form\DetailPageForm\UserEntityForm;
use Phoenix\Page\MenuItems\MenuItemsUsers;
use Phoenix\Report\Archive\ArchiveTableUserShifts;
use Phoenix\Report\Worker\WorkerTimeClockRecord;
use Phoenix\Report\Worker\WorkerWeeklySummary;

/**
 * @method User getEntity()
 *
 * Class DetailPageBuilderUser
 *
 * @author James Jones
 * @package Phoenix\DetailPage
 *
 */
class DetailPageBuilderUser extends DetailPageBuilder
{
    /**
     * @var string
     */
    private string $startDate;

    /**
     * @return UserFactory
     */
    protected function getNewEntityFactory(): UserFactory
    {
        return new UserFactory( $this->db, $this->messages );
    }

    /**
     * @return MenuItemsUsers
     */
    public function getMenuItems(): MenuItemsUsers
    {
        return new MenuItemsUsers( $this->getEntityFactory() );
    }

    /**
     * @return UserEntityForm
     */
    public function getForm(): UserEntityForm
    {
        return new UserEntityForm(
            $this->HTMLUtility,
            $this->getEntity()
        );
    }

    /**
     * @param string $startDate
     * @return $this
     */
    public function setStartDate(string $startDate = ''): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayEntityName(): string
    {
        $entity = $this->getEntity();
        return $entity->role === 'staff' ? 'worker' : $entity->entityName;
    }

    /**
     * @return $this
     */
    public function addReports(): self
    {
        $user = $this->getEntity();
        if ( !$user->exists ) {
            return $this;
        }

        $startDate = $this->startDate ?? '';
        $htmlUtility = $this->HTMLUtility;
        $format = $this->format;

        $shifts = $user->shifts->orderLatestToEarliest();
        $shift = (new ShiftFactory( $this->db, $this->messages ))->getNew();

        $reports = [
            (new ArchiveTableUserShifts(
                $htmlUtility,
                $format,
            ))
                ->setEntities( $user->shifts->getUnfinishedShifts()->getAll(), $shift )
                ->setTitle( 'Current Shifts' )
                ->setEmptyMessageClass( 'info')
                ->setEmptyMessage( ucfirst( $user->name ?? 'user' ) . ' is not currently clocked onto any shifts.' ),
            (new WorkerTimeClockRecord(
                $htmlUtility,
                $format,
            ))
                ->setStartAndFinishDates($startDate)
                ->setUsername( $user->name )
                ->setShifts( $shifts ),
            (new WorkerWeeklySummary(
                $htmlUtility,
                $format,
            ))
                ->setStartAndFinishDates($startDate)
                ->setUsername( $user->name )
                ->setShifts( $shifts ),
            (new ArchiveTableUserShifts(
                $htmlUtility,
                $format,
            ))
                ->setEntities( $shifts->getAll(), $shift )
                ->setTitle( 'All Worker Shifts' )
        ];
        $html = '';
        foreach ( $reports as $report ) {
            $html .= $report->render();
        }
        $this->page->addContent( $html );
        return $this;
    }
}