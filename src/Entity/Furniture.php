<?php

namespace Phoenix\Entity;

/**
 *
 * @property string $name
 * @property string $namePlural
 * @property string $quantity
 *
 * Class Furniture
 *
 * @package Phoenix
 */
class Furniture extends Entity
{
    /**
     * @var string Fontawesome icon
     */
    protected string $icon = 'chair';

    /**
     * @var string
     */
    protected string $_name;

    /**
     * @var string
     */
    protected string $_namePlural;

    /**
     * @var int Not set from db table. Set manually as per Job
     */
    protected int $_quantity;

    /**
     * Map of DB table columns.
     * Arrays keys are column names.
     * 'property' is matching Class property.
     * 'type' is data type for validation,
     * 'required' flags that data must be present to be added as DB row
     *
     * Don't include ID column in this array as it's added in constructor.
     *
     * @var array
     */
    protected array $_columns = [
        'name' => [
            'type' => 'name',
            'required' => true
        ],
        'plural_name' => [
            'type' => 'name',
            'property' => 'namePlural'
        ]
    ];

    /**
     * Not set from db table. Set manually as per Job
     *
     * @param int|null $quantity
     * @return int
     */
    protected function quantity(int $quantity = null): ?int
    {
        if ( $quantity !== null ) {
            $this->_quantity = $quantity;
        }
        return $this->_quantity ?? null;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function name(string $name = ''): string
    {
        if ( !empty( $name ) ) {
            $this->_name = $name;
        }
        return $this->_name ?? '';
    }

    /**
     * @param string $namePlural
     * @return string
     */
    protected function namePlural(string $namePlural = ''): string
    {
        if ( !empty( $namePlural ) ) {
            $this->_namePlural = $namePlural;
        }
        if ( !empty( $this->_namePlural ) ) {
            return $this->_namePlural;
        }
        if ( !empty( $this->name ) ) {
            return $this->_namePlural = $this->name . 's';
        }
        return '';
    }

    /**
     * @param bool $includeLink
     * @return string
     */
    public function getFurnitureString(bool $includeLink = true): string
    {
        $quantity = $this->quantity;
        if ( $quantity === 1 ) {
            $name = $this->name;
            if ( empty( $name ) ) {
                $name = '<strong>Unknown</strong>';
            }
        } elseif ( $quantity > 1 ) {
            $name = $this->namePlural;
        } elseif ( $quantity === null ) {
            $name = $this->namePlural;
            $quantity = '<strong>Unknown # of </strong>';
        }
        if ( empty( $name ) ) {
            $name = '<strong>Unknowns</strong>';
        }
        if ( $includeLink ) {
            $link = $this->getLink();
            if ( !empty( $link ) ) {
                $name = '<a class="text-white" href="' . $link . '">' . $name . '</a>';
            }
        }
        return '<span class="text-nowrap">' . $quantity . ' ' . $name . '</span>';

    }

    /**
     * @return bool
     */
    public function canDelete(): bool
    {
        $jobFactory = new JobFactory( $this->db, $this->messages );
        $jobs = $jobFactory->getAll();
        //$jobs = $jobFactory->addFurniture( $jobs );
        $numberOfJobs = 0;
        foreach ( $jobs as $job ) {
            if ( is_array( $job->furniture ) ) {
                foreach ( $job->furniture as $furniture ) {
                    if ( $furniture->id === $this->id ) {
                        $numberOfJobs++;
                        break;
                    }
                }
            }
        }
        if ( $numberOfJobs > 0 ) {
            return $this->addError( '<strong>' . $numberOfJobs . '</strong> jobs include this furniture. You cannot delete this furniture when an existing job includes it.' );
        }
        return true;
    }

    /**
     * @return array
     */
    public function healthCheck(): array
    {
        if ( empty( $this->name ) ) {
            $errors[] = 'Furniture' . $this->getIDBadge() . ' has no name.';
        }
        return $errors ?? [];
    }
}