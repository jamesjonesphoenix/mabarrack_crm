<?php


namespace Phoenix\Entity;


/**
 * @property string $name
 * @property string $value
 *
 * Class Setting
 *
 * @author James Jones
 * @package Phoenix\Entity
 *
 */
class Setting extends Entity
{
    /**
     * @var string
     */
    protected string $_name;

    /**
     * @var string
     */
    protected string $_value;


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
        'value' => [
            'type' => 'string',
        ]
    ];

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
     * @param string $value
     * @return string
     */
    protected function value(string $value = ''): string
    {
        if ( !empty( $value ) ) {
            $this->_value = $value;
        }
        return $this->_value ?? '';
    }



    /**
     * @return bool
     */
    public function canDeleteThisEntityType(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canCreate(): bool
    {
        return false;
    }
}