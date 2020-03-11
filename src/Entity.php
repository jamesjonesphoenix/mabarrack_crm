<?php


namespace Phoenix;

/**
 * @property bool $exists
 * @property integer $id
 *
 * @property string $tableName
 *
 * Class Entity
 *
 * @package Phoenix
 */
class Entity extends AbstractCRM
{
    /**
     * @var bool exists as entry in database.
     */
    protected $_exists;

    /**
     * @var integer
     */
    protected $_id;

    /**
     * @var string
     */
    protected $_tableName = '';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array|integer $input Can be either a numeric ID or an array of args
     * @return bool
     */
    public function init($input = null): bool
    {
        if ( is_numeric( $input ) ) {
            $row = $this->db->getRow( $this->tableName, array('ID' => $input) );
            if ( empty( $row ) ) {
                return $this->exists = false;
            }
        } else {
            $row = $input;
        }

        if ( empty( $row['ID'] ) && $row['ID'] !== 0 ) {
            return $this->exists = false;
        }

        foreach ( $row as $key => $item ) {
            if ( $item === null ) {
                continue;
            }
            $property = $key;
            //if ( $property === 'ID' ) {
            //  $property = 'id';
            //}
            //convert 'camel_case' to 'camelCase'
            $property = ucwords( $property, '_' );
            $property = lcfirst( $property );
            $property = str_replace( '_', '', $property );

            if ( method_exists( $this, $property ) ) {
                $this->$property = $item;
            }
        }

        $this->data = $row;

        return $this->exists = true;
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
     * @param int $id
     * @return int
     */
    protected function id(int $id = null): int
    {
        if ( isset( $id ) && is_int($id)) {
            $this->_id = $id;
        }
        return $this->_id ?? 0;
    }

    /**
     * @return string
     */
    protected function tableName(): string
    {
        return $this->_tableName ?? '';
    }
}