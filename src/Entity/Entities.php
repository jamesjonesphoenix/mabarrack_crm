<?php


namespace Phoenix\Entity;

/**
 * Class Entities
 *
 * Helper methods for manipulating arrays of Shift() instances
 *
 * @author James Jones
 * @package Phoenix\Entity
 *
 */
class Entities
{
    /**
     * @var Entity[]
     */
    protected array $entities;

    /**
     * Entities constructor.
     *
     * @param Entity[] $entities
     */
    public function __construct(array $entities = [])
    {
        $this->entities = $entities;
    }

    /**
     * @return Entity[]
     */
    public function getAll(): array
    {
        return $this->entities ?? [];
    }

    /**
     * @param int|null $id
     * @return Entity|null
     */
    public function getOne(int $id = null): ?Entity
    {
        if ( $id !== null ) {
            return $this->entities[$id] ?? null;
        }
        if ( empty( $this->entities ) ) {
            return null;
        }
        return current( $this->entities );
    }

    /**
     * @param int $id
     * @return $this
     */
    public function removeOne(int $id): self
    {
        $entity = $this->getOne( $id );
        if ( $entity !== null ) {
            unset( $this->entities[$entity->id] );
        }
        return $this;
    }

    /**
     * @param int $max
     * @return $this
     */
    public function getEntitiesWithErrors(int $max = 9999): self
    {
        $i = 0;
        foreach ( $this->entities as $entity ) {
            if ( !empty( $entity->healthCheck() ) ) {
                $i++;
                $errorEntities[$entity->id] = $entity;
                if ( $i === $max ) {
                    return new self( $errorEntities );
                }
            }
        }
        return new self( $errorEntities ?? [] );
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return count( $this->entities );
    }

    /**
     * @param Entity $entity
     * @return array
     */
    public function addOrReplace(Entity $entity): array
    {
        $entities = $this->entities;
        if ( empty( $entities[$entity->id] ) ) {
            return [$entity->id => $entity] + $entities;
        }
        $entities[$entity->id] = $entity;
        return $entities;
    }
}