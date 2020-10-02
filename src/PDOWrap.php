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
     * @var null|PDOWrap
     */
    protected static ?PDOWrap $_instance = null;

    /**
     * @var PDO
     */
    protected PDO $pdo;

    /**
     * @var Messages
     */
    protected Messages $messages;

    /**
     * @var array
     */
    protected array $tables;

    /**
     * @var array
     */
    private array $params;

    /**
     * @param array         $params
     * @param Messages|null $messages
     * @return PDOWrap
     */
    public static function instance(array $params = [], Messages $messages = null): PDOWrap
    {
        if ( self::$_instance === null ) {
            self::$_instance = new self( $params, $messages );
        }
        return self::$_instance;
    }

    /**
     * PDOWrap constructor.
     *
     * @param array    $params
     * @param Messages $messages
     */
    protected function __construct(array $params, Messages $messages)
    {
        $this->messages = $messages;
        $this->params = $params;
        $this->connectPDO();
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
        return call_user_func_array( [$this->pdo, $method], $args );
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return []; //Pass the names of the variables that should be serialised here
    }

    /**
     *
     */
    private function connectPDO(): void
    {
        $default_options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        $params = $this->params;
        $this->pdo = new PDO(
            'mysql:host=' . $params['host'] . ';dbname=' . $params['name'] . ';port=' . $params['port'] . 'charset=utf8',
            $params['user'],
            $params['password'],
            $default_options
        );
    }

    /**
     *
     */
    public function __wakeup()
    {
        $this->connectPDO();
    }

    /**
     * Helper function to run prepared statements
     *
     * @param string $sql
     * @param null   $args
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
     * @param string       $table
     * @param array|string $columns
     * @param array|string $queryArgs
     * @return array|false
     */
    public function getRow(string $table = '', $queryArgs = [], $columns = 'all')
    {
        $array = $this->get( $table, $queryArgs, $columns, 1 );
        if ( $array !== false ) {
            return array_shift( $array );
        }
        return $array;
    }

    /**
     * @param string       $table
     * @param array|string $queryArgs
     * @param array|string $columns
     * @param int          $limit
     * @param string       $orderBy
     * @return array|bool
     */
    public function getRows(string $table = '', $queryArgs = [], $columns = 'all', int $limit = 0, string $orderBy = '')
    {
        return $this->get( $table, $queryArgs, $columns, $limit, $orderBy );
    }

    /**
     * @param string $table
     * @param array  $queryArgs
     * @return int|null
     */
    public function getCount(string $table = '', $queryArgs = []): ?int
    {
        if ( empty( $table ) ) {
            $this->messages->add( '<strong>Database:</strong> No table name supplied to count method.<br>' . print_r( $queryArgs, true ) );
            return null;
        }

        $sql = 'SELECT COUNT(*) as number FROM ' . $table;
        $sql .= $this->getWhereSQLFragment( $queryArgs );
        $args = $this->getRunArgs( $queryArgs );
        //d($sql);
        //d($args);
        //d(debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 ));
        $statement = $this->run( $sql, $args );

        return $statement->fetch()['number'] ?? null;
    }

    /**
     * @param array|string $columns
     * @return string
     */
    private function getColumnSQLFragment($columns = 'all'): string
    {
        if ( empty( $columns ) ) {
            $this->messages->add( '<strong>Database:</strong> Column name input missing from query.' );
            return '';
        }
        if ( is_array( $columns ) ) {
            return implode( ', ', $columns );
        }
        if ( is_string( $columns ) ) {
            if ( in_array( $columns, ['all', '*'] ) ) {
                return '*';
            }
            return $columns;
        }
        $this->messages->add( '<strong>Database:</strong> Column name input in wrong format.' );
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
        if ( !is_array( $queryArgs ) ) {
            return '';
        }

        $sqlWhereStrings = [];
        foreach ( $queryArgs as $columnName => $queryArg ) {
            $sqlWhereString = $columnName;
            if ( is_array( $queryArg ) ) {

                switch( $queryArg['operator'] ) {
                    case 'IN':
                        $pieces = [];
                        foreach ( $queryArg['value'] as $key => $arg ) {
                            $pieces[] = ':' . $columnName . '_' . $key;
                        }
                        $sqlWhereString .= ' IN (' . implode( ',', $pieces ) . ')';
                        break;
                    //case '!=':
                    //  $sqlWhereString .= 'blag';
                    //break;
                    case 'BETWEEN':
                        //$sqlWhereString .= ' BETWEEN ' . $queryArg['value']['start'] . ' AND ' . $queryArg['value']['finish'] ;
                        $sqlWhereString .= ' BETWEEN :' . $columnName . '_start'  . ' AND :' . $columnName . '_finish' ;
                        break;
                    default:
                        $sqlWhereString .= ' ' . $queryArg['operator'] . ' :' . $columnName;
                        break;
                }

            } else if ( $queryArg === null || strtolower( $queryArg ) === 'null' ) {
                $sqlWhereString .= ' IS NULL';
            } else {
                $sqlWhereString .= '=:' . $columnName;
            }
            $sqlWhereStrings[] = $sqlWhereString;
        }

        return $sql . implode( ' AND ', $sqlWhereStrings );

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
        foreach ( $queryArgs as $columnName => $queryArg ) {
            //echo $columnName . ' ' .  print_r($queryArg, true) .   '<br>';
            if ( isset( $queryArg ) && !is_array( $queryArg ) ) {
                $queryArg = ['value' => $queryArg];
            }

            if ( isset( $queryArg['value'] )
                && $queryArg['value'] !== null
                && (!is_string($queryArg['value']) || strtolower( $queryArg['value'] ) !== 'null')
            ) {
                switch( $queryArg['operator'] ?? '' ) {
                    case 'LIKE':
                        $args[$columnName] = '%' . $queryArg['value'] . '%';
                        break;
                    case 'IN':
                        foreach ( $queryArg['value'] as $key => $arg ) {
                            $args[$columnName . '_' . $key] = $arg;
                        }
                        break;
                    case 'BETWEEN':
                        $args[$columnName . '_start'] = $queryArg['value']['start'];
                        $args[$columnName . '_finish'] = $queryArg['value']['finish'];
                        break;
                    default:
                        $args[$columnName] = $queryArg['value'];
                }

            }

        }
        return $args ?? [];
    }

    /**
     * @param string       $table
     * @param array|string $queryArgs
     * @param array|string $columns
     * @param int          $limit
     * @param string       $orderBy
     * @return array|bool
     */
    private function get(string $table = '', $queryArgs = [], $columns = 'all', int $limit = 0, string $orderBy = '')
    {
        if ( empty( $table ) ) {
            $this->messages->add( '<strong>Database:</strong> No table name supplied to get method.<br>' . print_r( $queryArgs, true ) );
            return false;
        }
        if ( !$columnString = $this->getColumnSQLFragment( $columns ) ) {
            return false;
        }

        $sql = 'SELECT ' . $columnString . ' FROM ' . $table;

        $sql .= $this->getWhereSQLFragment( $queryArgs );
        $args = $this->getRunArgs( $queryArgs );
        if ( !empty( $orderBy ) ) {
            $sql .= ' ORDER BY ' . $orderBy . ' DESC';
        }
        if ( $limit > 0 ) {
            $sql .= ' LIMIT ' . $limit;
        }
       // d($sql);
       // d($args);
       // d(debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 ));
        $statement = $this->run( $sql, $args );

        $result = $statement->fetchAll();

        if ( !empty( $result ) ) {
            return $result;
        }
        return false;
    }

    /**
     * @param string $table
     * @param array  $data
     * @return bool|string
     */
    public function add(string $table = '', array $data = [])
    {
        if ( !$this->tableExists( $table ) ) {
            return false;
        }

        $sql = 'INSERT INTO ' . $table . ' (';
        $sql .= implode( ',', array_keys( $data ) );
        $sql .= ') VALUES (';
        $dataStrings = [];
        foreach ( $data as $columnName => $value ) {
            if ( $value === null || strtolower( $value ) === 'null' ) {
                $dataStrings[] = 'NULL';
            } else {
                $dataStrings[] = ':' . $columnName;
            }
        }
        $sql .= implode( ', ', $dataStrings );
        $sql .= ')';

        $args = $this->getRunArgs( $data );

        if ( empty( $args ) ) {
            return $this->pdo->query( $sql );
        }
        $statement = $this->pdo->prepare( $sql );
        $result = $statement->execute( $args );
        if ( empty( $result ) ) {
            return false;
        }
        return $this->pdo->lastInsertId();
    }

    /**
     *
     *
     * @param string $table
     * @param array  $data
     * @param array  $queryArgs
     * @return bool|int Returns number of rows updated or false if query failed.
     */
    public function update(string $table = '', array $data = [], array $queryArgs = [])
    {
        if ( !$this->tableExists( $table ) ) {
            return false;
        }
        if ( empty( $queryArgs ) ) {
            return false;
        }
        $sql = 'UPDATE ' . $table . ' SET ';
        $dataStrings = [];
        foreach ( $data as $columnName => $value ) {
            if ( $value === null || strtolower( $value ) === 'null' ) {
                $dataStrings[] = $columnName . '=NULL';
            } else {
                $dataStrings[] = $columnName . '=:' . $columnName;
            }
        }
        $sql .= implode( ', ', $dataStrings );

        $sql .= $this->getWhereSQLFragment( $queryArgs );
        $args = array_merge( $this->getRunArgs( $data ), $this->getRunArgs( $queryArgs ) );

        if ( empty( $args ) ) {
            return false;
            //$statement = $this->pdo->query( $sql );
        }
        $statement = $this->pdo->prepare( $sql );
        $result = $statement->execute( $args );
        if ( !empty( $result ) ) {
            return $statement->rowCount();
        }
        return $result;
    }

    /**
     * @param string $table
     * @param array  $queryArgs
     * @return bool
     */
    public function delete(string $table = '', array $queryArgs = []): bool
    {
        if ( !$this->tableExists( $table ) ) {
            return false;
        }
        if ( empty( $queryArgs ) ) {
            return false;
        }

        $sql = 'DELETE FROM ' . $table . $this->getWhereSQLFragment( $queryArgs );
        $args = $this->getRunArgs( $queryArgs );

        if ( empty( $args ) ) {
            return $this->pdo->query( $sql );
        }
        $statement = $this->pdo->prepare( $sql );
        if ( $statement->execute( $args ) ) {
            return true;
        }
        return false;
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