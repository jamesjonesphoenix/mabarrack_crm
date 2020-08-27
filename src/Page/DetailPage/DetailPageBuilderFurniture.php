<?php


namespace Phoenix\Page\DetailPage;


use Phoenix\Entity\FurnitureFactory;
use Phoenix\Form\FurnitureForm;

/**
 *
 * Class DetailPageBuilderFurniture
 *
 * @author James Jones
 * @package Phoenix\DetailPage
 *
 */
class DetailPageBuilderFurniture extends DetailPageBuilder
{


    /**
     * @return FurnitureFactory
     */
    protected function getNewEntityFactory(): FurnitureFactory
    {
        return new FurnitureFactory( $this->db, $this->messages );
    }


    /**
     * @return FurnitureForm
     */
    public function getForm(): FurnitureForm
    {
        return new FurnitureForm(
            $this->HTMLUtility,
            $this->getEntity()
        );
    }
}