<?php

namespace Phoenix;

/**
 *
 * @property string $name
 * @property string $quantity
 *
 * Class Furniture
 *
 * @package Phoenix
 */
class Furniture extends Entity
{
    /**
     * @var string
     */
    protected $_name;

    /**
     * @var string Not set from db table. Set manually as per Job
     */
    protected $_quantity;

    /**
     * @var string
     */
    protected $_tableName = 'furniture';

    /**
     * Not set from db table. Set manually as per Job
     *
     * @param int $quantity
     * @return int
     */
    protected function quantity(int $quantity = 0): int
    {
        if ( !empty( $quantity ) ) {
            $this->_quantity = $quantity;
        }
        return $this->_quantity ?? 0;
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
        return $this->_name ?? 'Unknown';
    }

    /**
     * @return string
     */
    public function getFurnitureString(): string
    {
        $string = $this->quantity . ' ' . $this->name;
        if (  $this->quantity !== 1 ) {
            $string .= 's';
        }
        return $string ?? 'Unknown';

    }

}