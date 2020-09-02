<?php


namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\ActivityFactory;
use Phoenix\Entity\FurnitureFactory;
use Phoenix\Entity\JobFactory;
use Phoenix\Entity\Shift;
use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\UserFactory;
use Phoenix\Form\DetailPageForm\ShiftEntityForm;
use Phoenix\Page\MenuItems\MenuItemsShifts;

/**
 * @method Shift getEntity()
 *
 * Class DetailPageBuilderShift
 *
 * @author James Jones
 * @package Phoenix\DetailPage
 *
 */
class DetailPageBuilderShift extends DetailPageBuilder
{
    /**
     * @return ShiftFactory
     */
    protected function getNewEntityFactory(): ShiftFactory
    {
        return new ShiftFactory( $this->db, $this->messages );
    }

    /**
     * @return MenuItemsShifts
     */
    public function getMenuItems(): MenuItemsShifts
    {
        return new MenuItemsShifts( $this->getEntityFactory() );
    }

    /**
     * @return ShiftEntityForm
     */
    public function getForm(): ShiftEntityForm
    {
        $entity = $this->getEntity();
        return (new ShiftEntityForm(
            $this->HTMLUtility,
            $entity
        ))->makeOptionsDropdownFields(
            (new JobFactory( $this->db, $this->messages ))->getOptionsArray(),
            (new UserFactory( $this->db, $this->messages ))->getOptionsArray(),
            (new ActivityFactory( $this->db, $this->messages ))->getOptionsArray(),
            $this->getFurnitureOptions()
        );
    }

    /**
     * @return array
     */
    private function getFurnitureOptions(): array
    {
        $entity = $this->getEntity();
        if ( !empty( $entity->job->furniture ) ) {
            $furnitureFactory = new FurnitureFactory( $this->db, $this->messages );
            $furniture = $furnitureFactory->getEntities(

                ['id' => ['operator' => 'IN', 'value' => array_column( $entity->job->furniture, 'ID', 'ID' )]]
            );
            $furnitureOptions = array_column( $furniture, 'name', 'id' );
        }
        if ( !$entity->exists || $entity->job->id === 0 || $entity->activity->factoryOnly === true ) {
            return $furnitureOptions ?? [];
        }

        $furnitureID = $entity->furniture->id ?? null;

        if ( empty( $furnitureOptions[$furnitureID] ) ) {
            $furnitureOptions[$furnitureID] = $entity->furniture->name;
        }
        return $furnitureOptions ?? [];
    }
}