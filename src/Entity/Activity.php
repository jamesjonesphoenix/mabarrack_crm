<?php

namespace Phoenix\Entity;

/**
 * @property string $category
 * @property bool   $chargeable
 * @property bool   $deactivated
 * @property bool   $factoryOnly
 * @property string $image
 * @property string $name
 * @property string $displayName
 * @property string $options
 * @property string $type
 *
 * Class Activity
 *
 * @package Phoenix
 */
class Activity extends Entity
{
    /**
     * @var string
     */
    protected string $_category;

    /**
     * @var bool
     */
    protected bool $_chargeable;

    /**
     * @var bool
     */
    protected bool $_deactivated;

    /**
     * @var bool
     */
    protected bool $_factoryOnly;

    /**
     * @var string
     */
    protected string $_image;

    /**
     * @var string
     */
    protected string $_name;

    /**
     * @var string
     */
    protected string $_options;

    /**
     * @var string
     */
    protected string $_displayName;

    /**
     * @var string
     */
    protected string $_type;

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
        'category' => [
            'type' => 'name'
        ],
        'type' => [
            'type' => 'name',
        ],
        'options' => [
            'type' => 'string',
        ],
        'chargable' => [
            'type' => 'flag',
            'property' => 'chargeable'
        ],
        'image' => [
            'type' => 'string',
        ],
        'factoryOnly' => [
            'type' => 'flag',
            'property' => 'factoryOnly'
        ],
        'deactivated' => [
            'type' => 'flag'
        ],
    ];

    /**
     * @param string $displayName
     * @return string
     */
    protected function displayName(string $displayName = ''): string
    {
        if ( !empty( $displayName ) ) {
            return $this->_displayName = trim($displayName);
        }
        if ( !empty( $this->_displayName ) ) {
            return $this->_displayName;
        }
        return $this->_displayName = $this->name;
    }

    /**
     * @param bool|null $chargeable
     * @return bool
     */
    protected function chargeable(bool $chargeable = null): ?bool
    {
        if ( $chargeable !== null ) {
            $this->_chargeable = $chargeable;
        }
        return $this->_chargeable ?? null;
    }

    /**
     * @param string $category
     * @return string
     */
    protected function category(string $category = ''): string
    {
        if ( !empty( $category ) ) {
            $this->_category = $category;
        }
        return $this->_category ?? '';
    }

    /**
     * @param bool $deactivated
     * @return bool
     */
    protected function deactivated(bool $deactivated = null): ?bool
    {
        if ( $deactivated !== null ) {
            $this->_deactivated = $deactivated;
        }
        return $this->_deactivated ?? null;
    }

    /**
     * @return bool
     */
    public function isActive(): ?bool
    {
        if ( $this->deactivated === null ) {
            return null;
        }
        return $this->deactivated ? false : true;

    }

    /**
     * @param bool $factoryOnly
     * @return bool
     */
    protected function factoryOnly(bool $factoryOnly = null): ?bool
    {
        if ( $factoryOnly !== null ) {
            $this->_factoryOnly = $factoryOnly;
        }
        return $this->_factoryOnly ?? null;
    }

    /**
     * @param string $image
     * @return string
     */
    protected function image(string $image = ''): string
    {
        if ( !empty( $image ) ) {
            $this->_image = $image;
        }
        return $this->_image ?? '';
    }

    /**
     * @param string $name
     * @return string
     */
    protected function name(string $name = ''): string
    {
        if ( !empty( $name ) ) {
            $this->_name = trim($name);
        }
        return $this->_name ?? '';
    }

    /**
     * @param string $options
     * @return string
     */
    protected function options(string $options = ''): string
    {
        if ( !empty( $options ) ) {
            $this->_options = $options;
        }
        return $this->_options ?? '';
    }

    /**
     * @param string $type
     * @return string
     */
    protected function type(string $type = ''): string
    {
        if ( !empty( $type ) ) {
            $this->_type = $type;
        }
        return $this->_type ?? '';
    }

    /**
     * @return bool
     */
    public function canDeleteThisEntityType(): bool
    {
        return false;
    }
}