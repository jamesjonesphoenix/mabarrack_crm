<?php


namespace Phoenix\Page;

use Phoenix\Entity\CustomerFactory;
use Phoenix\Entity\FurnitureFactory;
use Phoenix\Entity\JobFactory;
use Phoenix\Entity\SettingFactory;
use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\UserFactory;
use Phoenix\Page\MenuItems\MenuItemsEntities;
use Phoenix\Page\MenuItems\MenuItemsJobs;
use Phoenix\Page\MenuItems\MenuItemsOther;
use Phoenix\Page\MenuItems\MenuItemsReports;
use Phoenix\Page\MenuItems\MenuItemsShifts;
use Phoenix\Page\MenuItems\MenuItemsUsers;

/**
 * Class IndexPageBuilder
 *
 * @author James Jones
 * @property IndexPage $page
 *
 * @package Phoenix\Page
 *
 */
class IndexPageBuilder extends PageBuilder
{
    /**
     * @return $this
     */
    public function buildPage(): self
    {
        $this->page = $this
            ->getNewPage()
            ->setHeadTitle( 'Main Menu' );
        $this->addMenu();
        return $this;
    }

    /**
     * @return IndexPage
     */
    protected function getNewPage(): IndexPage
    {
        return new IndexPage( $this->HTMLUtility );
    }

    /**
     * @return $this
     */
    private function addMenu(): self
    {
        // $countErrors = true;
        $this->page->setMainMenu( [
            'Jobs' => ((new MenuItemsJobs(
                new JobFactory( $this->db, $this->messages ) ))->setJobUrgencyThreshold( new SettingFactory( $this->db, $this->messages )
            ))->includeAddNew(),
            'Shifts' => (new MenuItemsShifts(
                new ShiftFactory( $this->db, $this->messages )
            ))->includeAddNew(),
            'Users' => (new MenuItemsUsers(
                new UserFactory( $this->db, $this->messages )
            ))->includeAddNew(),
            'Customers' => (new MenuItemsEntities(
                new CustomerFactory( $this->db, $this->messages )
            ))->includeAddNew(),
            'Furniture' => (new MenuItemsEntities(
                new FurnitureFactory( $this->db, $this->messages )
            ))->includeAddNew(),
            'Report' => new MenuItemsReports(),
            'Other' => new MenuItemsOther()

        ] );
        return $this;
    }


}