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
use Phoenix\Page\MenuItems\MenuItemsReports;
use Phoenix\Page\MenuItems\MenuItemsShifts;
use Phoenix\Page\MenuItems\MenuItemsUsers;

/**
 * Class IndexPageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class IndexPageBuilder extends PageBuilder
{
    /**
     * @var IndexPage
     */
    protected IndexPage $page;

    /**
     * @return $this
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage();
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
        $countErrors = true;

        $jobFactory = new JobFactory( $this->db, $this->messages );
        $shiftFactory = new ShiftFactory( $this->db, $this->messages );
        $customerFactory = new CustomerFactory( $this->db, $this->messages );
        $userFactory = new UserFactory( $this->db, $this->messages );
        $furnitureFactory = new FurnitureFactory( $this->db, $this->messages );
        //
        $this->page->setMainMenu( [
            'Jobs' => [
                'icon' => $jobFactory->getNew()->getIcon(),
                'contextual_class' => 'job',
                'items' => (new MenuItemsJobs( $jobFactory ))
                    ->setJobUrgencyThreshold( new SettingFactory( $this->db, $this->messages ) )
                    ->getMenuItems( $countErrors )
            ],
            'Shifts' => [
                'icon' => $shiftFactory->getNew()->getIcon(),
                'contextual_class' => 'shift',
                'items' => (new MenuItemsShifts( $shiftFactory ))->getMenuItems( $countErrors )
            ],
            'Users' => [
                'icon' => $userFactory->getNew()->getIcon(),
                'contextual_class' => 'worker',
                'items' => (new MenuItemsUsers( $userFactory ))->getMenuItems( $countErrors )
            ],
            'Customers' => [
                'icon' => $customerFactory->getNew()->getIcon(),
                'contextual_class' => 'customer',
                'items' => (new MenuItemsEntities( $customerFactory ))->getMenuItems( $countErrors )
            ],
            'Furniture' => [
                'icon' => $furnitureFactory->getNew()->getIcon(),
                'contextual_class' => 'furniture',
                'items' => (new MenuItemsEntities( $furnitureFactory ))->getMenuItems( $countErrors )
            ],
            'Report' => [
                'icon' => $this->HTMLUtility::getIconHTML( 'clipboard-list' ),
                'contextual_class' => 'report',
                'items' => (new MenuItemsReports())->getMenuItems()
            ]
        ] );
        return $this;
    }


}