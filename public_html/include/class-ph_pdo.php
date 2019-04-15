<?php

class ph_PDO
{
    protected static $_instance = NULL;
    protected $pdo;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected function __construct( /*$dsn, $username = NULL, $password = NULL, $options = []*/ ) {
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

    // a proxy to native PDO methods
    public function __call( $method, $args ) {
        return call_user_func_array( array( $this->pdo, $method ), $args );
    }

    //helper function to run prepared statements
    public function run( $sql, $args = NULL ) {
        if ( empty( $args ) ) {
            return $this->pdo->query( $sql );
        }
        $stmt = $this->pdo->prepare( $sql );
        $stmt->execute( $args );
        return $stmt;
    }

    //returns 1 row
    function get_row( $table, $columns = 'all', $query = null ) {
        return $this->get_data( $table, $columns, $query, true );

    }

    function get_rows( $table, $columns = 'all', $query = null ) {
        return $this->get_data( $table, $columns, $query, false );
    }

    private function get_data( $table, $columns = 'all', $query_args = null, $single_row = false ) {
        $sql = 'SELECT ';
        if ( is_array( $columns ) ) {
            $sql .= implode( ', ', $columns );

        } elseif ( in_array( $columns, array( 'all', '*' ) ) ) {
            $sql .= '*';
        } else {
            ph_messages()->add_message( 'Wrong columns specified when getting row from DB.' );
            return false;
        }
        $sql .= ' FROM ' . $table;
        $args = null;
        if ( !empty( $query_args ) ) {
            $sql .= ' WHERE ';
            if ( is_string( $query_args ) )
                $sql .= $query_args;
            elseif ( is_array( $query_args ) ) {
                $sql_where_strings = array();
                foreach ( $query_args as $key => $query_arg ) {
                    if ( is_array( $query_arg ) ) {
                        $operator = $query_arg[ 'operator' ];
                        $args[ $key ] = $query_arg[ 'value' ];
                    } else {
                        $operator = '=';
                        $args[ $key ] = $query_arg;
                    }
                    $sql_where_strings[] .= $key . $operator . ':' . $key;
                    //array( 'value' => 0, 'operator' => '!=' ) ))

                }
                $sql .= implode( ' AND ', $sql_where_strings );
            }
        }
        $statement = $this->run( $sql, $args );
        if ( $single_row )
            $result = $statement->fetch();
        else
            $result = $statement->fetchAll();
        if ( !empty( $result ) ) {
            return $result;
        }
        return false;
    }
}

function ph_pdo() {
    return ph_PDO::instance();
}