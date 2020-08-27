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
     * Shifts constructor.
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
     * @param int $max
     * @return Entity[]
     */
    public function getEntitiesWithErrors(int $max = 9999): array
    {
        $i = 0;
        foreach ( $this->entities as $entity ) {
            if ( !empty( $entity->healthCheck() ) ) {
                $i++;
                $errorEntities[$entity->id] = $entity;
                if ( $i === $max ) {
                    return $errorEntities;
                }
            }
        }
        return $errorEntities ?? [];
    }
}