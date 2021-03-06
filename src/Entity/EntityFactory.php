<?php

namespace Phoenix\Entity;

use Phoenix\AbstractCRM;

/**
 * Class EntityFactory
 */
abstract class EntityFactory extends AbstractCRM
{
    /**
     * @var string
     */
    protected string $className = '';

    /**
     * @var string Used in messages and stuff
     */
    protected string $entityName = '';

    /**
     * @var string Used in messages and stuff
     */
    protected string $entityNamePlural = '';

    /**
     * @var string
     */
    protected string $tableName = '';

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName ?? '';
    }

    /**
     * @return string
     */
    public function getEntityNamePlural(): string
    {
        if ( !empty( $this->entityNamePlural ) ) {
            return $this->entityNamePlural;
        }
        return $this->entityNamePlural = $this->getEntityName() . 's' ?? '';
    }

    /**
     * DB table name
     *
     * @return string
     */
    public function getTableName(): string
    {
        if ( !empty( $this->tableName ) ) {
            return $this->tableName;
        }
        return $this->tableName = $this->getEntityNamePlural();
    }

    /**
     * @return Entity[]
     */
    public function getAll(): array
    {
        return $this->getEntities();
    }

    /**
     * @param array      $queryArgs
     * @param bool|array $provision
     * @return Entity[]
     */
    public function getEntities(array $queryArgs = [], $provision = false): array
    {
        $entities = $this->instantiateEntitiesFromDB( $queryArgs );
        if ( empty( $provision ) || empty( $entities ) ) {
            return $entities;
        }
        return $this->provisionEntities( $entities, $provision );
    }

    /**
     * @param Entity[]   $entities
     * @param bool|array $provision
     * @return Entity[]
     */
    public function provisionEntities(array $entities = [], $provision = false): array
    {
        return $entities;
    }

    /**
     * @param Entity     $entity
     * @param bool|array $provision
     * @return Entity
     */
    public function provisionEntity(Entity $entity, $provision = false): Entity
    {
        return $this->provisionEntities( [$entity->id => $entity], $provision )[$entity->id];
    }

    /**
     * @param int        $id
     * @param bool|array $provision
     * @return Entity|null
     */
    public function getEntity(int $id = 0, $provision = true): ?Entity
    {
        return $this->getEntities( ['ID' => $id], $provision )[$id] ?? null;
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
        $options = array_column( $this->getAll(), 'name', 'id' );
        asort($options);
        return $options;
    }



    /**
     * @return Entity
     */
    public function getNew(): Entity
    {
        //$entity = $this->instantiateEntityClass()->init(); old
        $entity = $this->instantiateEntityClass();
        $entity->entityName = $this->getEntityName();
        $entity->entityNamePlural = $this->getEntityNamePlural();
        $entity->tableName = $this->getTableName();
        return $entity;
    }

    /**
     * @return Entity
     */
    abstract protected function instantiateEntityClass(): Entity;

    /**
     * @param array $queryArgs
     * @return Entity[]
     */
    protected function instantiateEntitiesFromDB(array $queryArgs = []): array
    {
        if ( !empty( $queryArgs['limit'] ) ) {
            $limit = $queryArgs['limit'];
            unset( $queryArgs['limit'] );
        }
        if ( !empty( $queryArgs['order_by'] ) ) {
            $orderBy = $queryArgs['order_by'];
            unset( $queryArgs['order_by'] );
        }

        $rows = $this->db->getRows(
            $this->getTableName(),
            $queryArgs,
            'all',
            $limit ?? 0,
            $orderBy ?? ''
        );

        if ( empty( $rows ) ) {
            return [];
        }
        foreach ( $rows as $row ) {
            $instance = $this->instantiateEntityClass()->init( $row );
            $instance->entityName = $this->getEntityName();
            $instance->entityNamePlural = $this->getEntityNamePlural();
            $instance->tableName = $this->getTableName();
            $instances[$row['ID']] = $instance;
        }
        return $instances ?? [];
    }

    /**
     * @param array $queryArgs
     * @return int|null
     */
    public function getCount(array $queryArgs = []): ?int
    {
        return $this->db->getCount( $this->getTableName(), $queryArgs );
    }

    /**
     * @return int|null
     */
    public function countAll(): ?int
    {
        return $this->db->getCount( $this->getTableName() );
    }

    /**
     * @param int $max
     * @return int|null
     */
    public function countAllWithErrors(int $max = 9999): ?int
    {
        return (new Entities( $this->getAll() ))
            ->getEntitiesWithErrors( $max )
            ->getCount();
    }

    /**
     * Adds property to Entities where property is a single Entity. For example adding the Job to a Shift.
     *
     * @param Entity[]           $entities Entity instances
     * @param EntityFactory|null $additionFactory Factory to create the array of additions
     * @param array|bool         $provisionArgs
     * @param string             $joinPropertyName Required if Entity property name is different to addition class name. Analogous to field in DB JOIN query.
     * @return Entity[]
     */
    protected function addOneToOneEntityProperties(array $entities = [], EntityFactory $additionFactory = null, $provisionArgs = false, string $joinPropertyName = ''): array
    {
        if ( empty( $entities ) || $additionFactory === null ) {
            return $entities;
        }
        if ( empty( $joinPropertyName ) ) {
            $joinPropertyName = $additionFactory->getEntityName();
        }


        $additionIDs = $this->getEntityIDs( $entities, $joinPropertyName );
        if ( empty( $additionIDs ) ) {
            //d($entities);
            //$this->messages->add('At least one ID should have been returned');
            return $entities;
        }
        $additions = $additionFactory->getEntities(
            $this->getPropertyQueryArgs( $additionIDs ),
            $provisionArgs
        );

        foreach ( $entities as &$entity ) {

            $id = $entity->$joinPropertyName->id;
            if ( !empty( $additions[$id] ) ) {
                $entity->$joinPropertyName = $additions[$id];
            }
        }
        return $entities;
    }

    /**
     * Adds property to Entities where property is an array of Entities. For example adding Shifts to Jobs.
     *
     * @param Entity[]           $entities Entity instances
     * @param EntityFactory|null $additionFactory Factory to create the array of additions
     * @param array|bool         $provisionArgs
     * @param string             $joinPropertyName Required if addition property name is different to Entity class name. Analogous to field in DB JOIN query.
     * @return Entity[]
     */
    protected function addManyToOneEntityProperties(array $entities = [], EntityFactory $additionFactory = null, $provisionArgs = false, $joinPropertyName = ''): array
    {
        if ( empty( $entities ) || $additionFactory === null ) {
            return $entities;
        }
        $entityIDs = $this->getEntityIDs( $entities );

        if ( empty( $joinPropertyName ) ) {
            $joinPropertyName = strtolower( $this->getEntityName() );
            if ( empty( $joinPropertyName ) ) {
                return $entities;
            }
        }
        $propertyQueryArgs = $this->getPropertyQueryArgs(
            $entityIDs,
            $joinPropertyName
        );

        if ( empty( $propertyQueryArgs ) ) {
            return $entities;
        }
        $additions = $additionFactory->getEntities( $propertyQueryArgs, $provisionArgs ); //provision additions in all uses so far

        $propertyName = $additionFactory->getTableName();


        foreach ( $additions as $key => $addition ) {
            $id = $addition->$joinPropertyName->id;
            $addition->$joinPropertyName = $entities[$id];
            $sortedAdditions[$id][$addition->id] = $addition;
        }
        foreach ( $entities as $entity ) {
            $entity->$propertyName = $sortedAdditions[$entity->id] ?? [];
        }
        return $entities;
    }

    /**
     * @param Entity[] $entities
     * @param string   $propertyName
     * @return array
     */
    public function getEntityIDs(array $entities = [], string $propertyName = 'id'): array
    {
        foreach ( $entities as $entity ) {
            //echo $propertyName;
            if ( $propertyName !== 'id' ) {
                $entity = $entity->$propertyName;
            }
            $id = $entity->id;
            if ( $id !== null ) { // Don't throw errors if id can't be found because there's legit scenarios where it won't be found - eg non-existant Furniture for a factory Shift
                $entityIDs[$id] = $id;
            }
        }
        return $entityIDs ?? [];
    }

    /**
     * @param array  $arrayIDs
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


    /**
     * @param array|bool $provision
     * @param string     $property
     * @return bool
     */
    protected function canProvision($provision, string $property = ''): bool
    {
        return $provision === true || !empty( $provision[$property] );
    }
}