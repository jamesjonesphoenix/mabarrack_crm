<?php


namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\User;
use Phoenix\Entity\UserFactory;
use Phoenix\Form\DetailPageForm\UserEntityForm;
use Phoenix\Page\MenuItems\MenuItemsUsers;
use Phoenix\URL;

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
     * @return string
     */
    public function getDisplayEntityName(): string
    {
        $entity = $this->getEntity();
        switch( $entity->role ) {
            case 'staff':
                return 'employee';
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

        $shifts = $user->shifts->orderLatestToEarliest();
        $shift = (new ShiftFactory( $this->db, $this->messages ))->getNew();

        $nameBadge = !empty( $user->getFirstName() ) ? $this->HTMLUtility::getBadgeHTML( $user->getFirstName() ) . ' ' : '';
        $reports = [
            $this->getReportClient()->getFactory()->archiveTables()->getShifts()
                ->setEntities(
                    $user->shifts->getUnfinishedShifts()
                )
                ->setTitle( $nameBadge . 'Current Shifts' )
                ->setEmptyMessageClass( 'info' )
                ->setEmptyMessage(
                    ucfirst( $user->getFirstName() ) . ' is not currently clocked onto any shifts.'
                )
                ->setDummyEntity( $shift ),

            $this->getReportClient()->getFactory()->archiveTables()->getShifts()
                ->setEntities( $shifts )
                ->setTitle( $nameBadge . 'All Employee Shifts' )
                ->setGroupByForm(
                    $this->getGroupByForm(),
                    $this->groupBy
                )
                ->disablePrintButton()
                ->setDummyEntity( $shift )
                ->editColumn( 'employee', ['hidden' => true] )

        ];



        foreach ( $reports as $report ) {

            $report->addNavLink( 'add_new', [
                'content' => 'Add New Shift to ' . $this->HTMLUtility::getBadgeHTML($user->getFirstName()),
                'href' => (
                new URL( $report->getNavLinks()['add_new']['href'] ?? '')
                )
                    ->setQueryArg('prefill',['employee' => $user->id])
                    ->write()
            ] );

            $this->page->addContent(
                $report->render()
            );
        }
        return $this;
    }
}