<?php

namespace Phoenix;

/**
 * Class FurnitureFactory
 */
class FurnitureFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected $className = 'Furniture';

    /**
     * @var string
     */
    protected $tableName = 'furniture';

    /**
     * Alias for getEntities()
     *
     * @param array $queryArgs
     * @param bool $provision
     * @return Furniture[]
     */
    public function getFurniture(array $queryArgs = [], $provision = false): array
    {
        return $this->getEntities( $queryArgs, $provision );
    }

    /**
     * Alias for getEntities()
     *
     * @param int $id
     * @return Furniture
     */
    public function getOneFurniture(int $id = 0): Furniture
    {
        return $this->getEntities( ['ID' => $id], true )[$id];
    }

    /**
     * @param array $queryArgs
     * @param bool $provision Not used in FurnitureFactory
     * @return Furniture[]
     */
    public function getEntities(array $queryArgs = [], $provision = false): array
    {
        return $this->getClassesFromDBWrapper(  $queryArgs );
    }

    /**
     * @return Furniture
     */
    protected function instantiateEntityClass(): Entity
    {
        return new Furniture( $this->db, $this->messages );
    }

    /**
     * @param array $queryArgs
     * @return Furniture[]
     */
    protected function getClassesFromDBWrapper(array $queryArgs = []): array
    {
        return $this->instantiateEntitiesFromDB($queryArgs);
    }
}