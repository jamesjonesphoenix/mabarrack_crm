<?php

namespace Phoenix\Entity;

use Phoenix\AbstractCRM;
use Phoenix\DateTimeUtility;
use Phoenix\Messages;
use Phoenix\PDOWrap;
use Phoenix\Utility\HTMLTags;
use function Phoenix\phValidateID;

/**
 * @author  James Jones
 * @property bool    $exists
 * @property integer $id
 * @property string  $link
 * @property string  $tableName
 * @property string  $entityName
 * @property string  $entityNamePlural
 *
 * Class Entity
 *
 * @package Phoenix
 * @property array   $columns
 */
abstract class Entity extends AbstractCRM
{
    /**
     * @var string
     */
    protected string $_entityName = '';

    /**
     * @var string
     */
    protected string $_entityNamePlural = '';

    /**
     * @var bool Exists as row in the database.
     */
    protected bool $_exists = false;

    /**
     * @var integer
     */
    protected int $_id;

    /**
     * @var string href to detail page
     */
    protected string $_link = '';

    /**
     * @var string
     */
    protected string $_tableName = '';

    /**
     * Array of flags to notify if an attribute has been changed since being pulled from DB
     *
     * @var array
     */
    protected array $changed = [];

    /**
     * @var bool Flag changed to true the first time init() is run
     */
    private bool $initialised = false;

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
    protected array $_columns = [];

    /**
     * @var string Fontawesome icon
     */
    protected string $icon = '';

    /**
     * Flag if property has changed when set to be checked when updating database
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $oldValue = $this->$name;
        parent::__set( $name, $value );
        $newValue = $this->$name;
        if ( $oldValue !== $newValue && (!($oldValue instanceof Entity) || $oldValue->id !== $newValue->id) ) {
            $this->changed[$name] = true;
        }
    }

    /**
     * Entity constructor.
     *
     * @param PDOWrap|null  $db
     * @param Messages|null $messages
     */
    public function __construct(PDOWrap $db = null, Messages $messages = null)
    {
        $this->columns = array_merge( $this->columns, ['ID' => ['type' => 'id']] );
        parent::__construct( $db, $messages );
    }

    /**
     * Fill out class properties from input array. Should only be done from class factory
     *
     * @param array|integer $input Can be either a numeric ID to search the DB or an array of args
     * @return $this
     */
    public function init(array $input = []): self
    {
        foreach ( $input as $key => $item ) {
            $this->setProperty( $key, $item );
        }
        /*
        if ( $this->id !== null ) {
            $this->exists = true;
        }

        if ( $this->exists && !$this->initialised ) { //Make sure we're recording changes on a fresh slate if this is an existing Entity from the DB.
            $this->changed = [];
        }
        $this->initialised = true;
        */
        if ( $this->id !== null ) {
            $this->exists = true;
            $this->changed = []; //Make sure we're recording changes on a fresh slate if this is an existing Entity from the DB.
        }
        return $this;
    }

    /**
     * For setting Entity properties related to DB table columns
     *
     * @param $property
     * @param $value
     */
    public function setProperty(string $property = '', $value = null): void
    {
        if ( !isset( $value ) || $value === null ) {
            return;
        }

        if ( !array_key_exists( $property, $this->columns ) ) {
            return;
        }

        $propertyType = $this->columns[$property]['type'] ?? 'id';
        $propertyName = $this->getColumnPropertyName( $property );

        if ( method_exists( $this, $propertyName ) ) {
            $value = $this->cleanInput( $value, $propertyType );

            if ( $value !== null ) {
                $this->$propertyName = $value;
            }
        }
    }

    /**
     * Convert database column name to corresponding class property.
     * Essentially we're converting 'camel_case' to 'camelCase' or looking up a predefined value
     *
     * @param string $columnName
     * @return string
     */
    public function getColumnPropertyName(string $columnName = ''): string
    {
        if ( !array_key_exists( $columnName, $this->columns ) ) {
            return '';
        }
        if ( !empty( $this->columns[$columnName]['property'] ) ) { //lookup
            return $this->columns[$columnName]['property'];
        }
        //convert 'camel_case' to 'camelCase'
        $propertyName = strtolower( $columnName );
        $propertyName = ucwords( $propertyName, '_' );
        $propertyName = lcfirst( $propertyName );
        $propertyName = str_replace( '_', '', $propertyName );

        return $propertyName ?? '';
    }

    /**
     * @param string $entityName
     * @return string
     */
    protected function entityName(string $entityName = ''): string
    {
        if ( !empty( $entityName ) ) {
            return $this->_entityName = $entityName;
        }
        if ( !empty( $this->_entityName ) ) {
            return $this->_entityName;
        }
        return $this->_entityName = strtolower( substr( strrchr( get_class( $this ), '\\' ), 1 ) ) ?? '';
    }

    /**
     * @param string $entityNamePlural
     * @return string
     */
    protected function entityNamePlural(string $entityNamePlural = ''): string
    {
        if ( !empty( $entityNamePlural ) ) {
            return $this->_entityNamePlural = $entityNamePlural;
        }
        if ( !empty( $this->_entityNamePlural ) ) {
            return $this->_entityNamePlural;
        }
        return $this->_entityNamePlural = $this->entityName . 's' ?? '';
    }

    /**
     * Is it an actual entity pulled from the DB?
     *
     * @param bool $exists
     * @return bool
     */
    protected function exists(bool $exists = null): bool
    {
        if ( isset( $exists ) ) {
            $this->_exists = $exists;
        }
        return $this->_exists ?? false;
    }

    /**
     * @param int|null $id
     * @return int
     */
    protected function id(int $id = null): ?int
    {
        if ( $id !== null ) {
            $this->_id = $id;
        }
        return $this->_id ?? null;
    }

    /**
     * @param string $tableName
     * @return string
     */
    protected function tableName(string $tableName = ''): string
    {
        if ( !empty( $tableName ) ) {
            return $this->_tableName = $tableName;
        }
        if ( !empty( $this->_tableName ) ) {
            return $this->_tableName;
        }
        return $this->_tableName = $this->entityNamePlural ?? '';
    }

    /**
     * Database columns when updating/adding. Don't need to include ID column in this array.
     *
     * @param array $columns
     * @return array
     */
    protected function columns(array $columns = []): array
    {
        if ( !empty( $columns ) ) {
            $this->_columns = $columns;
        }
        return $this->_columns ?? [];
    }

    /**
     *
     *
     * @param int|bool|null $id
     * @return string
     */
    public function getLink($id = null): string
    {
        $entityName = $this->entityName;
        if ( empty( $entityName ) ) {
            return '';
        }

        $link = 'index.php?page=detail&entity=' . $entityName;
        if ( $id === false ) {
            return $link;
        }
        if ( $id === null ) {
            $id = $this->id;
        }
        if ( $id === null ) {
            return '';
        }
        return $link . '&id=' . $id;
    }

    /**
     * @return string
     */
    public function getArchiveLink(): string
    {
        return 'index.php?page=archive&entity=' . $this->entityName;
    }

    /**
     * @return array
     */
    public function getCustomNavItems(): array
    {
        return [];
    }

    /**
     * Sets input type
     *
     * @param        $input
     * @param string $type
     * @return bool|false|float|int|string|null
     */
    public function cleanInput($input, $type = '')
    {
        switch( $type ) {
            case 'date':
                if ( !empty( $input ) && is_string( $input ) && DateTimeUtility::validateDate( $input ) ) {
                    return date( 'Y-m-d', strtotime( $input ) );
                }
                return '';
            case 'flag':
                if ( empty( $input ) ) {
                    return false;
                }
                return true;
            case 'id':
                if ( ($return = phValidateID( $input )) !== false ) {
                    return $return;
                }
                break;
            case 'float':
                return (float)$input;
            case 'int':
                return (int)$input;
            case 'name':
                return ucwords( (string)$input );
            case 'password':
            case 'string':
                return (string)$input;
            case 'time':
                if ( !empty( $input ) ) {
                    return date( 'H:i', strtotime( $input ) );
                }
                return '';
        }
        return null;
    }

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function getDataHTMLTable(array $data = []): string
    {
        if ( empty( $data ) ) {
            foreach ( $this->columns as $columnName => $column ) {
                $propertyName = $this->getColumnPropertyName( $columnName );
                $item = $this->$propertyName;
                if ( $item !== null ) {
                    $data[$columnName] = $this->$propertyName->id ?? $this->$propertyName;
                }
            }
        }
        foreach ( $data as $columnName => $item ) {
            if ( $columnName === 'password' ) {
                $value = '********************************';
            } elseif ( $item === null ) {
                $value = 'null';
            } elseif ( $item instanceof self ) {
                $value = $item->id;
            } else {
                $value = $item;
            }
            $tableData[] = [
                'column_name' => ucfirst( $this->getColumnNiceName( $columnName ) ),
                'value' => empty( $value ) && $value !== 0 ? '-' : $value
            ];
        }
        return (new HTMLTags())::getTableHTML( [
            'data' => $tableData ?? [],
            'columns' => ['column_name' => 'Property', 'value' => 'Value'],
            'class' => 'mt-2'
        ] );
    }

    /**
     * @param string $columnName
     * @return mixed|string|string[]
     */
    public function getColumnNiceName(string $columnName = '')
    {
        if ( !empty( $this->columns[$columnName]['nice_name'] ) ) {
            return $this->columns[$columnName]['nice_name'];
        }
        return str_replace( '_', ' ', $columnName );
    }

    /**
     * @param string $tense
     * @param string $action
     * @return string
     */
    protected function getActionString(string $tense = '', string $action = ''): string
    {
        switch( $action ) {
            case 'update':
                if ( $tense === 'past' ) {
                    $string = 'updated ';
                } else {
                    $string = 'update';
                }
                break;
            case 'create':
                if ( $tense === 'past' ) {
                    $string = 'created new';
                } else {
                    $string = 'create new';
                }
                break;
            case 'save':
            default:
                if ( $tense === 'past' ) {
                    $string = 'saved';
                } else {
                    $string = 'save';
                }
        }
        return $string . ' ' . $this->entityName;
    }

    /**
     * Add new DB row
     *
     * @param array $data
     * @return bool|int|mixed
     */
    private function addDBRow(array $data = [])
    {
        $action = 'create';
        if ( $this->exists ) {
            return $this->addError( "Can't " . $this->getActionString( 'present', $action ) . '. ' . ucfirst( $this->entityName )
                . ' <span class="badge badge-danger">ID: ' . $this->id . '</span> already exists.' );
        }
        if ( empty( $data ) ) {
            return $this->addError( "Can't " . $this->getActionString( 'present', $action ) . '. No data to put into database.' );
        }

        $result = $this->db->add( $this->tableName, $data );
        if ( phValidateID( $result ) ) {
            $this->id = phValidateID( $result );
            $this->messages->add(
                ucfirst( $this->getActionString( 'past', $action ) ) . ' <span class="badge badge-primary">ID: ' . $this->id . '</span>'
                , 'success'
            );
            $this->changed = [];
            return $this->id;
        }
        return $this->addError( 'Failed to ' . $this->getActionString( 'present', $action ) . '.' );
    }

    /**
     * Update existing DB row
     *
     * @param array $data
     * @return bool|int
     * @throws \Exception
     */
    private function updateDBRow(array $data = [])
    {
        $action = 'update';
        if ( !$this->exists ) {
            return $this->addError( "Can't " . $this->getActionString( 'present', $action ) . " because it doesn't exist." );
        }
        $idString = ' <span class="badge badge-primary">ID: ' . $this->id . '</span>';
        $messageNoChanges = 'No changes were made to ' . $this->entityName . $idString;


        if ( empty( $data ) ) {
            //if ( !$this->messages->isMessage() ) {
            $this->messages->add( $messageNoChanges, 'warning' );
            //}
            return false;
        }
        $result = $this->db->update( $this->tableName, $data, ['ID' => $this->id] );
        $dataHTMLTable = $this->getDataHTMLTable( $data );
        if ( $result === 0 ) {
            return $this->addError( $messageNoChanges . ' Attempted to update the following data:' . $dataHTMLTable );
        }
        if ( empty( $result ) ) {
            return $this->addError( 'Failed to ' . $this->getActionString( 'present', $action ) . $idString . ' Attempted to update the following data:' . $dataHTMLTable );
        }
        $this->messages->add( ucfirst( $this->getActionString( 'past', $action ) ) . $idString . ' Updated the following data:' . $dataHTMLTable, 'success' );
        $this->changed = [];
        return $result;
    }

    /**
     * To be overwritten by child classes with actual health checking code
     *
     * @return array
     */
    public function healthCheck(): array
    {
        return [];
    }

    /**
     * Get DB input array
     *
     * @return array
     */
    protected function getSaveData(): array
    {
        foreach ( $this->columns as $columnName => $column ) { //Get DB input array
            $propertyName = $this->getColumnPropertyName( $columnName );
            $propertyValue = $this->$propertyName;
            if ( !empty( $this->changed[$propertyName] ) ) {
                if ( $propertyValue instanceof self ) {
                    $data[$columnName] = $propertyValue->id;
                } else {
                    $data[$columnName] = $propertyValue;
                }
            }
        }
        return $data ?? [];
    }

    /**
     * ID badge <span> HTML
     *
     * @param int|null $id
     * @return string
     */
    public function getIDBadge(int $id = null): string
    {
        if ( $id === null ) {
            $id = $this->id;
        }
        return $id === null ? '' : ' <span class="badge badge-primary">ID: ' . $id . '</span>';
    }

    /**
     * Checks that DB input array includes required columns for DB row
     *
     * @param array $data
     * @return array
     */
    public function checkRequiredColumns(array $data = []): array
    {
        foreach ( $this->columns as $columnName => $column ) {
            if ( !empty( $column['required'] ) ) {
                $propertyName = $this->getColumnPropertyName( $columnName );
                $propertyValue = $this->$propertyName;
                if ( empty( $propertyValue ) && $propertyValue !== 0 ) {
                    $this->addError( print_r( $data, true ) );
                    $errors[] = "Can't " . $this->getActionString( 'present', 'save' ) . '. <strong>' . ucfirst( $this->getColumnNiceName( $columnName ) ) . '</strong> is required to be set.';
                }
            }
        }
        return $errors ?? [];
    }

    /**
     * Add new db row or update existing db row
     *
     * @return bool|int|mixed
     * @throws \Exception
     */
    public function save()
    {
        $data = $this->getSaveData();
        $errorString = '<h5 class="alert-heading">Can\'t save ' . $this->entityName . ' because of the following problems.</h5>';

        $errors = $this->checkRequiredColumns( $data );
        if ( !empty( $errors ) ) {
            return $this->addError( $errorString . HTMLTags::getListGroup( $errors ) );
        }

        if ( defined( 'DEBUG' ) && DEBUG === true ) {
            $dataToSave = !empty( $data ) ? $this->getDataHTMLTable( $data ) : 'None';
            $this->messages->add(
                'Entity - ' . $this->entityName
                . '<br>Changed - ' . print_r( $this->changed, true )
                . '<br>Data to save - ' . $dataToSave
                . '<br>All column properties - ' . $dataToSave,
                'info'
            );
        }
        $errors = $this->healthCheck();
        if ( !empty( $errors ) ) {
            return $this->addError( $errorString . HTMLTags::getListGroup( $errors ) );
        }

        if ( $this->exists ) {
            return $this->updateDBRow( $data );
        }
        $addRow = $this->addDBRow( $data );
        if ( is_int( $addRow ) ) {
            $this->id = $addRow;
            $this->exists = true;
        }
        return $addRow;
    }

    /**
     * @return string
     */
    public function getAssociatedEntities(): string
    {
        return '';
    }

    /**
     * @return bool
     */
    public function canDelete(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canDeleteThisEntityType(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canCreate(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function deleteDryRun(): bool
    {
        if ( !$this->exists ) {
            return $this->addError( "Can't delete " . $this->entityName . " because it doesn't exist." );
        }
        if ( !$this->canDeleteThisEntityType() ) {
            return $this->addError( 'Deleting <strong>' . $this->entityNamePlural . '</strong> is not allowed.' );
        }
        if ( !$this->canDelete() ) {
            return false;
        }

        $this->messages->add(
            '<p>Are you sure you want to delete this ' . $this->entityName . '?'
            . $this->getAssociatedEntities() . '</p>'
            . HTMLTags::getButton( [
                'class' => ['btn', 'btn-danger', 'mt-2'],
                'type' => 'button',
                'id' => 'delete-for-real',
                'content' => 'Yes, Delete ' . $this->entityName
            ] )
            , 'warning'
        );
        return true;
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        if ( !$this->exists ) {
            return $this->addError( "Can't delete " . $this->entityName . " because it doesn't exist." );
        }

        $mainString = $this->entityName;
        if ( method_exists( $this, 'name' ) ) {
            $mainString .= ' named ' . $this->name();
        }
        $mainString .= ' <span class="badge badge-primary">ID: ' . $this->id . '</span>';
        if ( $this->db->delete( $this->tableName, ['ID' => $this->id] ) ) {
            $this->messages->add( 'Deleted ' . $mainString, 'success' );
            return true;
        }
        $this->addError( 'Failed to delete ' . $mainString );
        return false;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        if ( empty( $this->icon ) ) {
            return '';
        }

        return HTMLTags::getIconHTML( $this->icon );
    }
}