<?php

namespace Phoenix;

/**
 * Class EntityFactory
 */
abstract class EntityFactory extends AbstractCRM
{

    /**
     * @var string
     */
    protected $className = '';

    /**
     * @var string
     */
    protected $tableName = '';

    /**
     * @return Entity[]
     */
    public function getAll(): array
    {
        return $this->getEntities();
    }

    /**
     * @param array $queryArgs
     * @param bool $provision
     * @return array
     */
    abstract public function getEntities(array $queryArgs = [], $provision = false): array;

    /**
     * @param int $id
     * @return mixed
     */
    public function getEntity(int $id = 0): Entity
    {
        return $this->getEntities( ['ID' => $id], true )[$id];
    }

    /**
     * @return Entity
     */
    abstract protected function instantiateEntityClass(): Entity;

    /**
     * Wrapper so we can add specific classname to PHPDoc in subclasses
     *
     * @param array $queryArgs
     * @return Entity[]
     */
    abstract protected function getClassesFromDBWrapper(array $queryArgs = []): array;

    /**
     * @param array $queryArgs
     * @return Entity[]
     */
    protected function instantiateEntitiesFromDB($queryArgs = []): array
    {
        $rows = PDOWrap::instance()->getRows( $this->tableName, $queryArgs );
        if ( empty( $rows ) ) {
            return [];
        }
        foreach ( $rows as $row ) {
            $instance = $this->instantiateEntityClass();
            $instance->init( $row );
            $instances[$row['ID']] = $instance;
        }
        return $instances ?? [];
    }

    /**
     * Adds property to Entities where property is a single Entity. For example adding the Job to a Shift.
     *
     * @param Entity[] $entities Entity instances
     * @param EntityFactory $additionFactory Factory to create the array of additions
     * @param string $joinPropertyName Required if Entity property name is different to addition class name. Analogous to field in DB JOIN query.
     * @return Entity[]
     */
    protected function addOneToOneEntityProperties(array $entities = [], EntityFactory $additionFactory = null, string $joinPropertyName = ''): array
    {
        if ( empty( $entities ) || $additionFactory === null ) {
            return $entities;
        }
        if ( empty( $joinPropertyName ) ) {
            $joinPropertyName = strtolower( $additionFactory->className );
        }
        $additionIDs = $this->getEntityIDs( $entities, $joinPropertyName );

        $propertyQueryArgs = $this->getPropertyQueryArgs( $additionIDs );
        $additions = $additionFactory->getEntities( $propertyQueryArgs );

        foreach ( $entities as &$entity ) {
            if ( !empty( $additions[$entity->$joinPropertyName] ) ) {
                $entity->$joinPropertyName = $additions[$entity->$joinPropertyName];
            }
        }
        return $entities;
    }

    /**
     * Adds property to Entities where property is an array of Entities. For example adding Shifts to Jobs.
     *
     * @param Entity[] $entities Entity instances
     * @param EntityFactory $additionFactory Factory to create the array of additions
     * @param string $joinPropertyName Required if addition property name is different to Entity class name. Analogous to field in DB JOIN query.
     * @return Entity[]
     */
    protected function addManyToOneEntityProperties(array $entities = [], EntityFactory $additionFactory = null, $joinPropertyName = ''): array
    {
        if ( empty( $entities ) || $additionFactory === null ) {
            return $entities;
        }

        $entityIDs = $this->getEntityIDs( $entities );

        if ( empty( $joinPropertyName ) ) {
            $joinPropertyName = strtolower( $this->className );
            if ( empty( $joinPropertyName ) ) {
                return $entities;
            }
        }

        $propertyQueryArgs = $this->getPropertyQueryArgs( $entityIDs, $joinPropertyName );
        $additions = $additionFactory->getEntities( $propertyQueryArgs, true ); //provision additions in all uses so far

        $propertyName = $additionFactory->tableName;

        foreach ( $entities as &$entity ) {
            $entityAdditions = [];
            foreach ( $additions as $id => $addition ) {
                if ( $addition->$joinPropertyName === $entity->id || $addition->$joinPropertyName->id === $entity->id ) {
                    $entityAdditions[$id] = $addition;
                }
            }

            $entity->$propertyName = $entityAdditions;
        }
        return $entities;
    }

    /**
     * @param array $entities
     * @param string $propertyName
     * @return array
     */
    protected function getEntityIDs(array $entities = [], string $propertyName = 'id'): array
    {
        foreach ( $entities as $entity ) {
            $entityIDs[$entity->$propertyName] = $entity->$propertyName;
        }
        return $entityIDs ?? [];
    }

    /**
     * @param array $arrayIDs
     * @param string $column
     * @return array
     */
    protected function getPropertyQueryArgs(array $arrayIDs = [], string $column = 'ID'): array
    {
        if ( empty( $arrayIDs ) ) {
            return [];
        }
        if ( count( $arrayIDs ) > 1 ) {
            $arg = ['operator' => 'IN', 'value' => $arrayIDs];
        } else {
            $arg = reset( $arrayIDs );
        }
        return [$column => $arg];
    }
}