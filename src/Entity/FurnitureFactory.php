<?php

namespace Phoenix\Entity;

/**
 * Class FurnitureFactory
 */
class FurnitureFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected string $entityName = 'furniture';

    /**
     * @var string Used in messages and stuff
     */
    protected string $entityNamePlural = 'furniture';

    /**
     * @return Furniture
     */
    protected function instantiateEntityClass(): Furniture
    {
        return new Furniture( $this->db, $this->messages );
    }

    /**
     * Queries DB for all entities.
     * Returns entities as array to make a <select> form field.
     * Used by Phoenix/EntityForm->getOptionDropdownFieldHTML()
     *
     * @return array [<option> value1 => <option> name1, <option> value2 => <option> name2, ...]
     */
    public function getOptionsArray(): array
    {
        $furniture = $this->getAll();
        $furnitureOptions = array_column( $furniture, 'name', 'id' );
        asort( $furnitureOptions );
        return $furnitureOptions;
    }
}