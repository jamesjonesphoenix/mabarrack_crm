<?php

namespace Phoenix;

use Phoenix\Shift;

/**
 *
 * @property int|Customer $customer
 * @property string $dateFinished
 * @property string $dateStarted
 * @property string $description
 * @property Furniture[] $furniture
 * @property integer $priority
 * @property float $salePrice
 * @property float $materialCost
 * @property float $contractorCost
 * @property float $spareCost
 * @property Shift[] $shifts
 * @property bool $status
 *
 * Class Job
 *
 * @package Phoenix
 */
class Job extends Entity
{
    /**
     * @var int|Customer
     */
    protected $_customer;

    /**
     * @var string
     */
    protected $_dateStarted;

    /**
     * @var string
     */
    protected $_dateFinished;

    /**
     * @var string
     */
    protected $_description;

    /**
     * @var array
     */
    protected $_furniture;

    /**
     * @var integer
     */
    protected $_priority;

    /**
     * @var integer
     */
    protected $_salePrice;

    /**
     * @var integer
     */
    protected $_materialCost;

    /**
     * @var integer
     */
    protected $_contractorCost;

    /**
     * @var integer
     */
    protected $_spareCost;

    /**
     * @var integer
     */
    protected $_status;

    /**
     * @var Shift[]
     */
    protected $_shifts;

    /**
     * @var string
     */
    protected $_tableName = 'jobs';

    /**
     * @param array $shifts
     * @return Shift[]
     */
    protected function shifts(array $shifts = []): array
    {
        if ( !empty( $shifts ) ) {
            $this->_shifts = $shifts;
        }
        return $this->_shifts ?? [];
    }

    /**
     * @param int|Customer $customer
     * @return int|Customer
     */
    protected function customer($customer = null)
    {
        if ( !empty( $customer ) || $customer === 0) {
            $this->_customer = $customer;
        }
        return $this->_customer ?? new Customer();
    }

    /**
     * @param string $dateStarted
     * @return string
     */
    protected function dateStarted(string $dateStarted = ''): string
    {
        if ( !empty( $dateStarted ) ) {
            $this->_dateStarted = $dateStarted;
        }
        return $this->_dateStarted ?? '';
    }

    /**
     * @param string $dateFinished
     * @return string
     */
    protected function dateFinished(string $dateFinished = ''): string
    {
        if ( !empty( $dateFinished ) ) {
            $this->_dateFinished = $dateFinished;
        }
        return $this->_dateFinished ?? '';
    }

    /**
     * @param string $description
     * @return string
     */
    protected function description(string $description = ''): string
    {
        if ( !empty( $description ) ) {
            $this->_description = $description;
        }
        return $this->_description ?? '';
    }

    /**
     * Converts database JSON string into array and stores array for later conversion to Furniture Class instances. Or just stores Furniture Class instances.
     *
     * @param string|Furniture[] $input
     * @return array|Furniture[]
     */
    protected function furniture($input = ''): array
    {
        if ( empty( $input ) ) {
            if ( !empty( $this->_furniture ) ) {
                return $this->_furniture;
            }
            return [];
            //return ['ID' => 0, 'Quantity' => 0];
            //$furnitureFactory = new FurnitureFactory( $this->db, $this->messages );
            //$singleFurniture = $furnitureFactory->getNewFurniture();
            //return [$singleFurniture->id => $singleFurniture];
        }
        if ( !is_string( $input ) ) {
            return $this->_furniture = $input ?? [];
        }

        $furnitureArray = json_decode( $input, true );

        foreach ( $furnitureArray as $item ) {
            $id = key( $item );
            $quantity = array_shift( $item );
            $furniture[$id] = ['ID' => $id, 'Quantity' => $quantity];
        }


        return $this->_furniture = $furniture ?? [];
    }

    /**
     * @param int $priority
     * @return int
     */
    protected function priority(int $priority = 0): int
    {
        if ( !empty( $priority ) ) {
            $this->_priority = $priority;
        }
        return $this->_priority ?? 0;
    }

    /***
     * @param float|string $amount
     * @return float|string
     */
    public function salePrice($amount = 'number')
    {
        return $this->dollarAmountProperty( 'salePrice', $amount );
    }

    /***
     * @param float|string $amount
     * @return float|string
     */
    public function materialCost($amount = 'number')
    {
        return $this->dollarAmountProperty( 'materialCost', $amount );
    }

    /***
     * @param float|string $amount
     * @return float|string
     */
    public function contractorCost($amount = 'number')
    {
        return $this->dollarAmountProperty( 'contractorCost', $amount );
    }

    /***
     * @param float|string $amount
     * @return float|string
     */
    public function spareCost($amount = 'number')
    {
        return $this->dollarAmountProperty( 'spareCost', $amount );
    }

    /**
     * @param string $property
     * @param float|string $amount Either the actual number to set property or format 'number' or 'string' form to return.
     * @return int|string|null
     */
    private function dollarAmountProperty(string $property = '', $amount = 'number')
    {

        $property = '_' . $property;

        if ( is_numeric( $amount ) ) {
            $this->$property = $amount;
        }
        if ( $amount === 'number' ) {
            return $this->$property ?? 0;
        }
        if ( $amount === 'string' ) {
            return Format::currency( $this->$property ) ?? '';
        }
        return null;
    }

    /**
     * @param string $status
     * @return string
     */
    protected function status(string $status = ''): string
    {
        if ( !empty( $status ) ) {
            $this->_status = $status;
        }
        return $this->_status ?? '';
    }

    function update()
    {

    }
}