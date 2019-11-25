<?php

namespace Phoenix;

use PDO;
use PDOStatement;

/**
 * Class PDO
 *
 * @package Phoenix
 */
class PDOWrap
{
    /**
     * @var PDOWrap|null
     */
    protected static $_instance;

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var Messages
     */
    protected $messages;

    /**
     * @var array
     */
    protected $tables;

    /**
     * @return PDOWrap
     */
    public static function instance(): PDOWrap
    {
        if ( self::$_instance === null ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * PDOWrap constructor.
     *
     * @param Messages|null $messages
     */
    protected function __construct(Messages $messages = null)
    {
        /*$dsn, $username = NULL, $password = NULL, $options = []*/
        $this->messages = $messages;

        $default_options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];

        //'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . DB_PORT . 'charset=utf8', DB_USER, DB_PASSWORD


        //$options = array_replace( $default_options, $options );
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . DB_PORT . 'charset=utf8';
        $this->pdo = new PDO( $dsn, DB_USER, DB_PASSWORD, $default_options );
        //parent::__construct( $dsn, $username, $password, $options );
    }


    /**
     * A proxy to native PDO methods
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array( array($this->pdo, $method), $args );
    }

    public function __sleep() {
        return []; //Pass the names of the variables that should be serialised here
    }

    public function __wakeup() {
        $default_options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . DB_PORT . 'charset=utf8';
        $this->pdo = new PDO( $dsn, DB_USER, DB_PASSWORD, $default_options );
    }


    /**
     * Helper function to run prepared statements
     *
     * @param string $sql
     * @param null $args
     * @return false|PDOStatement
     */
    public function run($sql = '', $args = null)
    {
        if ( empty( $args ) ) {
            return $this->pdo->query( $sql );
        }
        $statement = $this->pdo->prepare( $sql );
        $statement->execute( $args );
        return $statement;
    }

    /**
     * Returns a single row
     *
     * @param string $table
     * @param array|string $columns
     * @param array|string $queryArgs
     * @return array|bool
     */
    public function getRow(string $table = '', $queryArgs = [], $columns = 'all')
    {
        return $this->get( $table, $queryArgs, $columns, true );

    }

    /**
     * @param string $table
     * @param array|string $columns
     * @param array|string $queryArgs
     * @return array|bool
     */
    public function getRows(string $table = '', $queryArgs = [], $columns = 'all')
    {
        return $this->get( $table, $queryArgs, $columns, false );
    }

    /**
     * @param array|string $columns
     * @return string
     */
    private function getColumnSQLFragment($columns = 'all'): string
    {
        if ( empty( $columns ) ) {
            $this->messages->add( 'Column name input missing from query.' );
            return '';
        }
        if ( is_array( $columns ) ) {
            return implode( ', ', $columns );
        }
        if ( is_string( $columns ) ) {
            if ( in_array( $columns, array('all', '*') ) ) {
                return '*';
            }
            return $columns;
        }
        $this->messages->add( 'Column name input in wrong format.' );
        return '';
    }

    /**
     * @param array|string $queryArgs
     * @return string
     */
    private function getWhereSQLFragment($queryArgs = []): string
    {
        if ( empty( $queryArgs ) ) {
            return '';
        }

        $sql = ' WHERE ';
        if ( is_string( $queryArgs ) ) {
            return $sql . $queryArgs;
        }

        if ( is_array( $queryArgs ) ) {
            $sqlWhereStrings = [];
            foreach ( $queryArgs as $key => $queryArg ) {
                $sqlWhereString = $key;
                if ( is_array( $queryArg ) ) {
                    $sqlWhereString .= ' ' . $queryArg['operator'] . ' :' . $key;
                } else if ( $queryArg === null || $queryArg === 'NULL' ) {
                    $sqlWhereString .= ' IS NULL';
                } else {
                    $sqlWhereString .= '=:' . $key;
                }
                $sqlWhereStrings[] = $sqlWhereString;
            }
            return $sql . implode( ' AND ', $sqlWhereStrings );
        }
        return '';
    }

    /**
     * @param array $queryArgs
     * @return array
     */
    private function getRunArgs($queryArgs = []): array
    {
        if ( empty( $queryArgs ) || !is_array( $queryArgs ) ) {
            return [];
        }
        foreach ( $queryArgs as $key => $queryArg ) {
            if ( isset( $queryArg['value'] ) && $queryArg['value'] !== null && $queryArg['value'] !== 'NULL' ) {

                if ( $queryArg['operator'] === 'LIKE' ) {
                    $args[$key] = '%' . $queryArg['value'] . '%';
                } else {
                    $args[$key] = $queryArg['value'];
                }
            } elseif ( isset( $queryArg ) && $queryArg !== null && $queryArg !== 'NULL' ) {
                $args[$key] = $queryArg;
            }
        }
        return $args ?? [];
    }

    /**
     * @param string $table
     * @param array|string $columns
     * @param array|string $queryArgs
     * @param bool $singleRow
     * @return array|bool
     */
    private function get(string $table = '', $queryArgs = [], $columns = 'all', $singleRow = false)
    {
        if ( empty( $table ) ) {
            $this->messages->add( 'No table name supplied to get method.' );
            return false;
        }
        if ( !$columnString = $this->getColumnSQLFragment( $columns ) ) {
            return false;
        }

        $sql = 'SELECT ' . $columnString . ' FROM ' . $table;

        $sql .= $this->getWhereSQLFragment( $queryArgs );
        $args = $this->getRunArgs( $queryArgs );

        $statement = $this->run( $sql, $args );
        if ( $singleRow ) {
            $result = $statement->fetch();
        } else {
            $result = $statement->fetchAll();
        }


        if ( !empty( $result ) ) {
            return $result;
        }
        return false;
    }

    /**
     * @param string $table
     * @param array $data
     * @return bool
     */
    public function add(string $table = '', array $data = []): bool
    {
        if ( !$this->tableExists( $table ) ) {
            return false;
        }

        $sql = 'INSERT INTO ' . $table . ' (';
        $sql .= implode( ',', array_keys( $data ) );
        $sql .= ') VALUES (';
        $dataStrings = [];
        foreach ( $data as $key => $value ) {
            if ( $value === null || strtolower( $value ) === strtolower( 'NULL' ) ) {
                $dataStrings[] = 'NULL';
            } else {
                $dataStrings[] = ':' . $key;
            }
        }
        $sql .= implode( ', ', $dataStrings );
        $sql .= ')';

        $args = $this->getRunArgs( $data );

        if ( empty( $args ) ) {
            return $this->pdo->query( $sql );
        }
        $statement = $this->pdo->prepare( $sql );
        return $statement->execute( $args );
    }

    /**
     * @param string $table
     * @param array $data
     * @param array $queryArgs
     * @return bool
     */
    public function update(string $table = '', array $data = [], array $queryArgs = []): bool
    {
        if ( !$this->tableExists( $table ) ) {
            return false;
        }
        $sql = 'UPDATE ' . $table . ' SET ';
        $dataStrings = [];
        foreach ( $data as $key => $value ) {
            if ( $value === null || strtolower( $value ) === strtolower( 'NULL' ) ) {
                $dataStrings[] = $key . '=NULL';
            } else {
                $dataStrings[] = $key . '=:' . $key ;
            }
        }
        $sql .= implode( ', ', $dataStrings );

        $sql .= $this->getWhereSQLFragment( $queryArgs );
        $args = array_merge( $this->getRunArgs( $data ), $this->getRunArgs( $queryArgs ) );

        if ( empty( $args ) ) {
            return $this->pdo->query( $sql );
        }
        $statement = $this->pdo->prepare( $sql );
        return $statement->execute( $args );
    }

    /**
     * @param string $table
     * @param array $queryArgs
     * @return bool
     */
    public function delete(string $table = '', array $queryArgs = []): bool
    {
        if ( !$this->tableExists( $table ) ) {
            return false;
        }
        $sql = 'DELETE FROM ' . $table;
        $sql .= $this->getWhereSQLFragment( $queryArgs );
        $args = $this->getRunArgs( $queryArgs );

        if ( empty( $args ) ) {
            return $this->pdo->query( $sql );
        }
        $statement = $this->pdo->prepare( $sql );
        return $statement->execute( $args );
    }

    /**
     * @param $table
     * @return bool
     */
    public function tableExists(string $table = ''): bool
    {
        if ( empty( $this->tables ) ) {
            $statement = $this->run( 'SHOW TABLES' );
            $this->tables = $statement->fetchAll( PDO::FETCH_COLUMN, 'Tables_in_' . DB_NAME );
        }
        if ( in_array( $table, $this->tables, true ) ) {
            return true;
        }
        return false;
    }

    /**
     * @param string $table
     * @return array
     */
    public function getColumns(string $table = ''): array
    {
        if ( !$this->tableExists( $table ) ) {
            return [];
        }
        $result = $this->run( 'SHOW COLUMNS FROM ' . $table )->fetchAll( 0 );
        foreach ( $result as $row ) {
            $columns[] = $row['Field'];
        }
        return $columns ?? [];
    }


}