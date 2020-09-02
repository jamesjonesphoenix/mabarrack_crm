<?php


namespace Phoenix\Page\DetailPage;


use Phoenix\Entity\FurnitureFactory;
use Phoenix\Form\DetailPageForm\FurnitureEntityForm;

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
     * @return FurnitureEntityForm
     */
    public function getForm(): FurnitureEntityForm
    {
        return new FurnitureEntityForm(
            $this->HTMLUtility,
            $this->getEntity()
        );
    }
}