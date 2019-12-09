<?php

namespace Phoenix;

/**
 * @property string $category
 * @property string $chargeable
 * @property integer $deactivated
 * @property integer $factoryOnly
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
    protected $_category;

    /**
     * @var integer
     */
    protected $_chargeable;

    /**
     * @var integer
     */
    protected $_deactivated;

    /**
     * @var integer
     */
    protected $_factoryOnly;

    /**
     * @var string
     */
    protected $_image;

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var string
     */
    protected $_options;

    /**
     * @var integer
     */
    protected $_displayName;

    /**
     * @var string
     */
    protected $_type;

    /**
     * @var string
     */
    protected $_tableName = 'activities';

    /**
     * @return string
     */
    protected function displayName(): string
    {
        if ( !empty( $this->_displayName ) ) {
            return $this->_displayName;
        }

        $similarActivities = $this->db->getRows( 'activities', array('name' => $this->name), 'type' );

        foreach ( $similarActivities as $similarActivity ) {
            if ( $similarActivity['type'] !== $this->type ) {

                $displayType = $this->type === 'All' ? 'Unspecific' : $this->type;
                return $this->_displayName = $displayType . ' ' . $this->name;
            }
        }

        return $this->_displayName = $this->name;

    }

    /**
     * @param int $chargeable
     * @return int
     */
    protected function chargeable(int $chargeable = 0): int
    {
        if ( !empty( $chargeable ) ) {
            $this->_chargeable = $chargeable;
        }
        return $this->_chargeable ?? 0;
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
     * @param int $deactivated
     * @return bool
     */
    protected function deactivated(int $deactivated = null): bool
    {
        if ( isset( $deactivated ) ) {
            $this->_deactivated = $deactivated === 1;
        }
        return $this->_deactivated ?? null;
    }

    /**
     * @return bool
     */
    public function isActive(): ?bool
    {
        if ( !isset( $this->_deactivated ) ) {
            return null;
        }
        return $this->_deactivated ? false : true;

    }


    /**
     * @param int $factoryOnly
     * @return bool
     */
    protected function factoryOnly(int $factoryOnly = null): ?bool
    {
        if ( isset( $factoryOnly ) ) {
            $this->_factoryOnly = $factoryOnly;
        }
        if ( !isset( $this->_factoryOnly ) ) {
            return null;
        }
        return !empty( $this->_factoryOnly ) ? true : false;
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
            $this->_name = $name;
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
}