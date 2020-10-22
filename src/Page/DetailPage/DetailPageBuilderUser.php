<?php


namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\User;
use Phoenix\Entity\UserFactory;
use Phoenix\Form\DetailPageForm\UserEntityForm;
use Phoenix\Page\MenuItems\MenuItemsUsers;
use Phoenix\Report\Archive\ArchiveTableShifts;
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
     * @param array $inputArgs
     * @return $this
     */
    public function setInputArgs(array $inputArgs = []): self
    {
        $this->setStartDate( $inputArgs['start_date'] ?? '' );
        return parent::setInputArgs( $inputArgs );
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
        switch( $entity->role ) {
            case 'staff':
                return 'worker';
            case 'admin':
                return 'admin';
            default:
                return $entity->entityName;
        }
    }

    /**
     * @return $this
     */
    public function addReports(): self
    {
        $user = $this->getEntity();
        if ( !$user->exists || $user->shifts->getCount() === 0 /* || $user->role !== 'staff' */ ) {
            return $this;
        }

        $startDate = $this->startDate ?? '';
        $htmlUtility = $this->HTMLUtility;
        $format = $this->format;

        $shifts = $user->shifts->orderLatestToEarliest();
        $shift = (new ShiftFactory( $this->db, $this->messages ))->getNew();

        $nameBadge = !empty( $user->name ) ? $this->HTMLUtility::getBadgeHTML( $user->name ) . ' ' : '';

        $reports = [
            (new ArchiveTableShifts(
                $htmlUtility,
                $format,
            ))
                ->setEntities( $user->shifts->getUnfinishedShifts()->getAll(), $shift )
                ->setTitle( $nameBadge . 'Current Shifts' )
                ->setEmptyMessageClass( 'info' )
                ->setEmptyMessage( ucfirst( $user->name ?? 'user' ) . ' is not currently clocked onto any shifts.' ),
            (new WorkerTimeClockRecord(
                $htmlUtility,
                $format,
            ))
                ->setStartAndFinishDates( $startDate )
                ->setUsername( $user->name )
                ->setShifts( $shifts ),
            (new WorkerWeeklySummary(
                $htmlUtility,
                $format,
            ))
                ->setStartAndFinishDates( $startDate )
                ->setUsername( $user->name )
                ->setShifts( $shifts ),
            (new ArchiveTableShifts(
                $htmlUtility,
                $format,
            ))
                ->setEntities( $shifts->getAll(), $shift )
                ->setTitle( $nameBadge . 'All Worker Shifts' )
                ->setGroupByForm(
                    $this->getGroupByForm()
                        ->makeHiddenFields( ['start_date' => $startDate] ),
                    $this->groupBy
                )
                ->disablePrintButton()
                ->editColumn( 'worker', ['hidden' => true] )
        ];
        $html = '';
        foreach ( $reports as $report ) {
            $html .= $report->render();
        }
        $this->page->addContent( $html );
        return $this;
    }
}