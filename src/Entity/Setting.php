<?php


namespace Phoenix\Entity;


use Phoenix\Utility\HTMLTags;

/**
 * @author James Jones
 * @property string $value
 * @property string $description
 *
 * Class Setting
 *
 * @property string $name
 * @package Phoenix\Entity
 *
 */
class Setting extends Entity
{
    /**
     * @var string Fontawesome icon
     */
    protected string $icon = 'cog';

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
            'type' => 'string',
            'required' => true
        ],
        'description' => [
            'type' => 'string',
        ],
        'value' => [
            'type' => 'string',
        ]
    ];

    /**
     * @var string
     */
    protected string $_description;

    /**
     * Check that only value has been changed. We disallow any other properties being changed.
     *
     * @return bool|int|mixed
     * @throws \Exception
     */
    public function save()
    {
        foreach ( $this->changed as $property => $changed ) {
            if ( $property !== 'value' ) {
                $errors[] = "Can't edit <strong>" . $property . "</strong>, only setting's <strong>value</strong> can be edited.";
            }
        }
        if ( !empty( $errors ) ) {
            return $this->addError(
                '<h5 class="alert-heading">Can\'t save setting because of the following problems:</h5>'
                . HTMLTags::getListGroup( $errors )
            );
        }
        return parent::save();
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