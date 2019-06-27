<?php

function ph_sec_session_start() {
    $session_name = 'sec_session_id';   // Set a custom session name
    $secure = USING_SSL;
    // This stops JavaScript being able to access the session id.
    $httponly = true;
    // Forces sessions to only use cookies.
    if ( ini_set( 'session.use_only_cookies', 1 ) === FALSE ) {
        ph_redirect( 'login', array( 'message' => 'session_ini_failed' ) );
        exit();
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params( 43200, $cookieParams[ "path" ], $cookieParams[ "domain" ], $secure, $httponly );
    // Sets the session name to the one set above.
    session_name( $session_name );

    session_start();            // Start the PHP session

    if ( !empty( $_SESSION[ 'LAST_ACTIVITY' ] ) && ( time() - $_SESSION[ 'LAST_ACTIVITY' ] > 28800 ) ) {
        // last request was more than 8 hours ago
        session_unset();     // unset $_SESSION variable for the run-time
        session_destroy();   // destroy session data in storage
        ph_redirect( 'login', array( 'message' => 'login_timed_out' ) );
    } else {

        $_SESSION[ 'LAST_ACTIVITY' ] = time(); // update last activity time stamp

        if ( empty( $_SESSION[ 'CREATED' ] ) ) {
            $_SESSION[ 'CREATED' ] = time();
        } else if ( time() - $_SESSION[ 'CREATED' ] > 1800 && !defined( 'DOING_AJAX' ) ) {
            // session started more than 30 minutes ago
            session_regenerate_id();    // change session ID for the current session and invalidate old session ID
            $_SESSION[ 'CREATED' ] = time();  // update creation time
        }
    }
    //session_regenerate_id();    // regenerated the session, delete the old one.
}

function ph_redirect( $location = false, $args = false ) {
    if ( empty( $location ) )
        return false;
    $arg_string = '';
    if ( !empty( $args ) ) {
        $arg_string = '?';
        if ( is_array( $args ) ) {

            $num_args = count( $args );
            $i = 0;
            foreach ( $args as $arg_name => $arg_value ) {
                $arg_string .= $arg_name . '=' . $arg_value;
                if ( ++$i !== $num_args ) {
                    $arg_string .= '&';
                }
            }
        } elseif ( is_string( $args ) ) {
            $arg_string .= $args;
        }
    }

    //clean location variable
    $location = ph_sanitize_redirect( $location );
    if ( strpos( $location, '.php' ) === false )
        $location .= '.php';

    //redirect
    header( "Location: " . $location . $arg_string, true, 302 );
    exit();
}


function ph_get_user_ip() {
    if ( !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) {
        if ( strpos( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ], ',' ) > 0 ) {
            $addr = explode( ",", $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] );
            return trim( $addr[ 0 ] );
        } else {
            return $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
        }
    } else {
        return $_SERVER[ 'REMOTE_ADDR' ];
    }
}

/**
 * Taken verbatim from WordPress wp_sanitize_redirect() in pluggable.php
 *
 * Sanitizes a URL for use in a redirect.
 *
 * @since 2.3.0
 *
 * @param string $location The path to redirect to.
 * @return string Redirect-sanitized URL.
 **/
function ph_sanitize_redirect( $location ) {
    $regex = '/
		(
			(?: [\xC2-\xDF][\x80-\xBF]        # double-byte sequences   110xxxxx 10xxxxxx
			|   \xE0[\xA0-\xBF][\x80-\xBF]    # triple-byte sequences   1110xxxx 10xxxxxx * 2
			|   [\xE1-\xEC][\x80-\xBF]{2}
			|   \xED[\x80-\x9F][\x80-\xBF]
			|   [\xEE-\xEF][\x80-\xBF]{2}
			|   \xF0[\x90-\xBF][\x80-\xBF]{2} # four-byte sequences   11110xxx 10xxxxxx * 3
			|   [\xF1-\xF3][\x80-\xBF]{3}
			|   \xF4[\x80-\x8F][\x80-\xBF]{2}
		){1,40}                              # ...one or more times
		)/x';
    $location = preg_replace_callback( $regex, '_ph_sanitize_utf8_in_redirect', $location );
    $location = preg_replace( '|[^a-z0-9-~+_.?#=&;,/:%!*\[\]()@]|i', '', $location );
    $location = ph_kses_no_null( $location );

    // remove %0d and %0a from location
    $strip = array( '%0d', '%0a', '%0D', '%0A' );
    return _ph_deep_replace( $strip, $location );
}

/**
 * Taken verbatim from WordPress _wp_sanitize_utf8_in_redirect() in pluggable.php
 *
 * URL encode UTF-8 characters in a URL.
 *
 * @ignore
 * @since 4.2.0
 * @access private
 *
 * @see wp_sanitize_redirect()
 *
 * @param array $matches RegEx matches against the redirect location.
 * @return string URL-encoded version of the first RegEx match.
 */
function _ph_sanitize_utf8_in_redirect( $matches ) {
    return urlencode( $matches[ 0 ] );
}

/**
 * Taken Verbatim from WordPress wp_kses_no_null() in kses.php
 *
 * Removes any invalid control characters in $string.
 *
 * Also removes any instance of the '\0' string.
 *
 * @since 1.0.0
 *
 * @param string $string
 * @param array $options Set 'slash_zero' => 'keep' when '\0' is allowed. Default is 'remove'.
 * @return string
 */
function ph_kses_no_null( $string, $options = null ) {
    if ( !isset( $options[ 'slash_zero' ] ) ) {
        $options = array( 'slash_zero' => 'remove' );
    }

    $string = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string );
    if ( 'remove' == $options[ 'slash_zero' ] ) {
        $string = preg_replace( '/\\\\+0+/', '', $string );
    }

    return $string;
}

/**
 * Taken verbatim from WordPress _deep_replace() in formatting.php
 *
 * Perform a deep string replace operation to ensure the values in $search are no longer present
 *
 * Repeats the replacement operation until it no longer replaces anything so as to remove "nested" values
 * e.g. $subject = '%0%0%0DDD', $search ='%0D', $result ='' rather than the '%0%0DD' that
 * str_replace would return
 *
 * @since 2.8.1
 * @access private
 *
 * @param string|array $search The value being searched for, otherwise known as the needle.
 *                              An array may be used to designate multiple needles.
 * @param string $subject The string being searched and replaced on, otherwise known as the haystack.
 * @return string The string with the replaced svalues.
 */
function _ph_deep_replace( $search, $subject ) {
    $subject = (string)$subject;

    $count = 1;
    while ( $count ) {
        $subject = str_replace( $search, '', $subject, $count );
    }

    return $subject;
}

function ph_validate_number( $number ) {
    $number = (int)$number;
    if ( !is_numeric( $number ) )
        return false;
    if ( !is_int( $number ) )
        return false;
    if ( $number < 1 )
        return false;
    return $number;
}

function ph_script_filename( $suffix = false ) {
    return basename( $_SERVER[ 'SCRIPT_FILENAME' ], $suffix );
}

/**
 * INITIALISE CONNECTION TO CRMDB
 *
 * @return mysqli
 */
function init_crmdb() {
    // Try and connect to the database
    $conn = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT );

    // If connection was not successful, handle the error
    if ( $conn->connect_error ) {
        die( "<h1>Error establishing a database connection</h1><h2><a href='https://www.phoenixwebdev.com.au/contact-us/'>Please contact</a> your nearest friendly Phoenix Web developer.</h2><p>" . $conn->connect_error . "</p>" );
    }
    return $conn;

}

////  CLOSE CONNECTION TO CRMDB  ////
function close_crmdb( $conn ) {
    mysqli_close( $conn );
}

////  RETURN ARRAY OF FOREIGN KEYS  ////
function get_fks( $detail ) {
    $fks = get_rows( "fks", "" );

    if ( $detail ) {
        return $fks;
    } else {
        $fnks = [];
        foreach ( $fks as $fk ) {
            $fkns[] = $fk[ 'column_name' ];
        }
        return $fkns;
    }
}

/**
 * RETURN NAME OF DETAIL PAGE FOR A TABLE
 *
 * @param $table
 * @return bool
 */
function get_detailpage( $table ) {
    $dps = get_rows( "detail_page", "WHERE table_name = '" . $table . "'" );
    if ( $dps !== FALSE ) {
        return $dps[ 0 ][ 'page_name' ];
    } else {
        return false;
    }
}

////  RETURNS AN ARRAY OF THE COLUMNS IN A TABLE  ////
function get_columns( $table, $detail ) {
    $con = init_crmdb();
    if ( $con ) {
        $sql = "SHOW COLUMNS FROM " . $table;
        $result = mysqli_query( $con, $sql );
        close_crmdb( $con );
        if ( $result->num_rows > 0 ) {
            $columns = array();
            while ( $column = $result->fetch_assoc() ) {
                if ( $detail ) {
                    $columns[] = $column;
                } else {
                    $columns[] = $column[ 'Field' ];
                }
            }
            return $columns;
        }
    }
    return false;
}


function get_columns_qry( $query, $qa ) {
    $con = init_crmdb();
    $sql = "SELECT * FROM `qrys` WHERE name = '" . $query . "'";
    $rsql = "";
    $rows = array();
    $qresult = mysqli_query( $con, $sql );
    if ( $qresult->num_rows > 0 ) {
        while ( $row = $qresult->fetch_assoc() ) {
            $rsql = $row[ 'query' ]; //get the query
        }
        for ( $a = 0; $a < count( $qa ); $a++ ) {
            $rsql = str_replace( "arg" . $a, $qa[ $a ], $rsql );
        }
        //print_r($rsql);
        $result = mysqli_query( $con, $rsql );
        close_crmdb( $con );
        $cols = $result->fetch_fields();
        if ( count( $cols ) != 0 ) { //runs the query and get the rows
            foreach ( $cols as $col ) {
                $rows[] = $col->name;
            }
            return array_unique( $rows );
        } else {
            return false;
        }
    } else { //query failed
        close_crmdb( $con );
        echo "QUERY NAME INVALID\n";
        return false;
    }

}


//// RETURNS NUMBER OF NOTIFCATIONS FROM GIVEN QUERY
function get_notify_qry( $queryname ) {
    $qry_rows = get_rows( "qrys", "WHERE name = '" . $queryname . "'" );
    $qry_row = $qry_rows[ 0 ];
    $query = $qry_row[ "query" ];

    if ( $queryname == "jurg_count" ) {
        $joburg_th = get_rows( "settings", "WHERE name = 'joburg_th'" )[ 0 ][ 'value' ];
        $query = str_replace( "arg0", $joburg_th, $query );
    }
    $con = init_crmdb();
    $sql = $query;
    $result = mysqli_query( $con, $sql );
    $data = mysqli_fetch_assoc( $result );
    close_crmdb( $con );
    return $data[ 'num' ];
}

////  RETURNS ARRAY OF ROWS  ////
function get_rows( $table, $query = '' ) {

    //ph_pdo()->run( "SELECT * FROM ? WHERE type = ?", [ $table ] )->fetch();


    $conn = init_crmdb();
    $sql = "SELECT * FROM " . $table . " " . $query;
    $result = mysqli_query( $conn, $sql );
    close_crmdb( $conn );
    if ( $result->num_rows > 0 ) {
        $rows = array();
        while ( $row = $result->fetch_assoc() ) {
            $rows[] = $row;
        }
        //print_r( $rows );
        return $rows;
    }
    return false;
}

////  RETURN ARRAY OF ROWS OF COLUMNS SUPPLIED  ////
function get_rows_ext( $table, $columns, $query ) {
    $con = init_crmdb();
    $col_str = "";
    for ( $f = 0; $f < count( $columns ); $f++ ) {
        if ( $f == ( count( $columns ) - 1 ) ) {
            $col_str .= $columns[ $f ];
        } else {
            $col_str .= $columns[ $f ] . ", ";
        }
    }
    $sql = "SELECT " . $col_str . " FROM " . $table . " " . $query;
    $result = mysqli_query( $con, $sql );
    close_crmdb( $con );
    if ( $result->num_rows > 0 ) {
        $rows = array();
        while ( $row = $result->fetch_assoc() ) {
            $rows[] = $row;
        }
        return $rows;
    }
    return false;
}

////  RETURN ARRAY OF ROWS FROM CUSTOM QUERY  ////
function get_rows_qry( $query, $qa ) {
    //print_r($qa);
    $con = init_crmdb();
    $sql = "SELECT * FROM `qrys` WHERE name = '" . $query . "'";
    $rsql = "";
    $rows = array();
    $qresult = mysqli_query( $con, $sql );
    if ( $qresult->num_rows > 0 ) {
        while ( $row = $qresult->fetch_assoc() ) {
            $rsql = $row[ 'query' ]; //get the query
        }
        for ( $a = 0; $a < count( $qa ); $a++ ) {
            $rsql = str_replace( "arg" . $a, $qa[ $a ], $rsql );
        }
        //print_r($rsql);
        $result = mysqli_query( $con, $rsql );
        close_crmdb( $con );
        if ( $result->num_rows > 0 ) { //runs the query and get the rows
            while ( $row = $result->fetch_assoc() ) {
                $rows[] = $row;
            }
            return $rows;
        } else {
            return false;
        }
    } else { //query failed
        close_crmdb( $con );
        echo "QUERY NAME INVALID\n";
        return false;
    }
}

/**
 * GENERATE TABLES FROM $COLS AND $ROWS
 *
 * @param $cols columns for the table
 * @param $rows rows for the table
 * @param string $view_button_args table name
 * @param $canview
 * @return string
 */
function generate_table( $cols, $rows, $view_button_args = '' ) {
    $html_str = "<table class='tablesorter table table-hover'>\n  <thead>\n    <tr>";
    $first = true;
    //print_r($cols);
    //print_r($rows);
    $has_status = false;
    $colidx = 0;
    foreach ( $cols as $column ) {
        if ( $column != "status" ) {
            if ( in_array( $column, [ "ID", "priority" ] ) ) {
                $html_str .= "<th class='w50'>" . str_replace( '_', ' ', $column ) . "</th>";
            } else if ( $colidx == ( count( $cols ) - 1 ) ) {
                $html_str .= "<th class='desctd'>" . str_replace( '_', ' ', $column ) . "</th>";
            } else if ( in_array( $column, [ "worker", "name" ] ) ) {
                $html_str .= "<th class='w200'>" . str_replace( '_', ' ', $column ) . "</th>";
            } else {
                $html_str .= "<th class='w100'>" . str_replace( '_', ' ', $column ) . "</th>";
            }
        } else {
            $has_status = true;
        }
        $colidx++;
    }
    if ( !empty( $view_button_args ) && ph_current_user()->get()->get_role() == 'admin' ) {
        if ( !is_array( $view_button_args ) ) {
            $view_button_args = array(
                array( 'table' => $view_button_args, 'detail_page' => get_detailpage( $view_button_args ) ) );
        }

        foreach ( $view_button_args as $key => &$view_button ) {
            $view_button[ 'detail_page' ] = get_detailpage( $view_button[ 'table' ] );
            if ( ( !empty( $view_button[ 'detail_page' ] ) ) ) {
                $html_str .= "<td class='viewth'></td>";
            }
            if ( empty( $view_button[ 'column' ] ) ) {
                $view_button[ 'column' ] = 'ID';
            }
            if ( empty( $view_button[ 'text' ] ) ) {
                $view_button[ 'text' ] = 'View';
                $view_button[ 'text' ] .= ' ' . rtrim( $view_button[ 'table' ], 's' );
            }
        }
    }

    $html_str .= "</tr>\n  </thead>\n";
    $html_str .= "  <tbody>\n";

    if ( $rows === FALSE ) {
        $html_str .= '    <tr><td class="noresult" colspan="' . ( count( $cols ) + 1 ) . '">' . "no results found </td></tr>\n";
    } else {
        foreach ( $rows as $rkey => $row ) {

            if ( $has_status ) {
                $html_str .= "    <tr class='" . $row[ "status" ] . "'>";
            } else {
                $html_str .= "    <tr>";
            }
            foreach ( $cols as $column ) {
                //if ( empty( $row[ $column ] ) )
                //  $row[ $column ] = '';
                if ( $column != "status" ) {
                    if ( ( $column == "job" ) && ( $row[ $column ] == 0 ) ) { //row job id is 0 (internal)
                        $row[ $column ] = "Factory";
                    } else if ( $column == "furniture" ) {
                        $rjson = json_decode( $row[ $column ], true );
                        $rstr = "";

                        foreach ( $rjson as $ff ) {
                            $ffid = current( array_keys( $ff ) );
                            $ffq = reset( $ff );
                            $ffn = get_rows( "furniture", "WHERE ID = " . $ffid )[ 0 ][ 'name' ];
                            $rstr .= $ffq . " " . ucfirst( $ffn ) . ( $ffq > 1 ? "s<br>" : "<br>" );
                        }
                        $row[ $column ] = $rstr;
                    } else if ( ( strpos( $column, 'cost' ) !== false ) or ( strpos( $column, 'price' ) !== false ) or ( strpos( $column, 'rate' ) !== false ) ) {
                        $row[ $column ] = ph_format_currency( $row[ $column ] );
                    } else if ( strpos( $column, 'time' ) !== false ) {
                        if ( !empty( strtotime( $row[ $column ] ) ) )
                            $row[ $column ] = substr_replace( $row[ $column ], "", -3 );
                    } else if ( strpos( $column, 'date' ) !== false ) {

                        if ( !ph_DateTime::validate_date( $row[ $column ] ) )
                            $row[ $column ] = 'N/A';
                        else
                            $row[ $column ] = date( "d-m-Y", strtotime( $row[ $column ] ) );

                        //$row[ $column ] = '<span class="hidden-date">' . $row[ $column ] . '</span>' .  date( "d-m-Y", strtotime( $row[ $column ] ) );
                    }
                    $html_str .= "<td class='" . $column . "'>" . $row[ $column ] . "</td>";
                }
            }

            if ( !empty( $view_button_args ) && ph_current_user()->get()->get_role() == 'admin' ) {
                foreach ( $view_button_args as $key => &$view_button ) {
                    //print_r('"'.$row[ $view_button[ 'column' ] ] . '"<br>');
                    if ( !empty( $view_button[ 'detail_page' ] ) && !empty( $row[ $view_button[ 'column' ] ] ) && $row[ $view_button[ 'column' ] ] != '0' )
                        $html_str .= '<td class="w50"><a href="' . $view_button[ 'detail_page' ] . '?id=' . $row[ $view_button[ 'column' ] ] . '"><div class="btn btn-default viewbtn">' . $view_button[ 'text' ] . '</div></a></td>';
                    else
                        $html_str .= '<td>-</td>';

                }
            }
            $html_str .= "</tr></a>\n";
        }
    }
    $html_str .= "  </tbody>\n</table>\n";
    return $html_str;
}

////  CHECK IF TABLE EXISTS  ////
function table_exists( $table ) {
    $con = init_crmdb();
    $ex = mysqli_query( $con, 'select 1 from `' . $table . '` LIMIT 1' );
    close_crmdb( $con );
    if ( $ex !== FALSE ) {
        return true;
    } else {
        return false;
    }
}

////  RETURN HTML OF TABLE  ////
function get_table( $table, $qry ) {
    if ( table_exists( $table ) ) {
        $rows = get_rows( $table, $qry );
        if ( !$rows ) {
            return false;
        }
        return "\n" . generate_table( array_keys( $rows[ 0 ] ), $rows, $table );
    } else {
        return "table '" . $table . "' not found<br><br>";
    }
}

////  CHECK IF TABLE EXISTS AND RETURN HTML OF TABLE  ////
function get_table_ext( $table, $cols, $qry ) {
    if ( table_exists( $table ) ) {
        $columns = $cols;
        $rows = get_rows_ext( $table, $cols, $qry );
        if ( !$columns or !$rows ) {
            return false;
        }
        return "\n" . generate_table( $columns, $rows, $table );
    } else {
        return "table '" . $table . "' not found<br><br>";
    }
}

////  ADD ROW (TABLE,COLUMNS,DATA)  ////
function add_row( $table, $cols, $data ) {
    if ( table_exists( $table ) ) {
        $con = init_crmdb();
        $sql = "INSERT INTO " . $table . "  (";
        for ( $c = 0; $c < count( $cols ); $c++ ) {
            if ( $c == ( count( $cols ) - 1 ) ) {
                $sql .= $cols[ $c ];
            } else {
                $sql .= $cols[ $c ] . ", ";
            }
        }
        $sql .= ") VALUES (";
        for ( $d = 0; $d < count( $data ); $d++ ) {
            /*
            if (strpos($cols[$d], "date") !== FALSE) {
                $date = array_filter(explode("-", $data[$d]));
                if (count($date) > 1) {
                    $data[$d] = $date[2] . "-" . $date[1] . "-" . $date[0];
                } else {
                    $data[$d] = "NULL";
                }
            }
            */
            if ( $d == ( count( $data ) - 1 ) ) {
                if ( $data[ $d ] != "NULL" ) {
                    $sql .= "'" . mysqli_real_escape_string( $con, $data[ $d ] ) . "'";
                } else {
                    $sql .= mysqli_real_escape_string( $con, $data[ $d ] );
                }
            } else {
                if ( $data[ $d ] != "NULL" ) {
                    $sql .= "'" . mysqli_real_escape_string( $con, $data[ $d ] ) . "', ";
                } else {
                    $sql .= mysqli_real_escape_string( $con, $data[ $d ] ) . ", ";
                }
            }
        }
        $sql .= ")";
        //echo $sql;
        $qry = mysqli_query( $con, $sql );
        if ( $qry ) {
            return true;
        } else {
            $stre = "<b>Error: " . $sql . "<br>" . mysqli_error( $con ) . "</b>";
            close_crmdb( $con );
            return $stre;
        }
    }
}

////  UPDATE ROW (TABLE,COLUMNS,DATA)  ////
function update_row( $table, $cols, $data ) {
    if ( table_exists( $table ) ) {
        $con = init_crmdb();
        $sql = "UPDATE " . $table . " SET ";
        for ( $c = 1; $c < count( $cols ); $c++ ) {
            if ( $c == ( count( $cols ) - 1 ) ) {
                if ( $data[ $c ] != "NULL" ) {
                    $sql .= $cols[ $c ] . " = '" . mysqli_real_escape_string( $con, $data[ $c ] ) . "'";
                } else {
                    $sql .= $cols[ $c ] . " = " . mysqli_real_escape_string( $con, $data[ $c ] );
                }
            } else {
                if ( $data[ $c ] != "NULL" ) {
                    $sql .= $cols[ $c ] . " = '" . mysqli_real_escape_string( $con, $data[ $c ] ) . "', ";
                } else {
                    $sql .= $cols[ $c ] . " = " . mysqli_real_escape_string( $con, $data[ $c ] ) . ", ";
                }
            }
        }
        $sql .= " WHERE ID = " . $data[ 0 ];
        $qry = mysqli_query( $con, $sql );
        if ( $qry ) {
            return true;
        } else {
            $stre = "<b>Error: " . $sql . "<br>" . mysqli_error( $con ) . "</b>";
            close_crmdb( $con );
            return $stre;
        }
    }
}

////  GENERATE FORM WITH $columns TO ADD / EDIT AN ENTRY TO $table  ////
function generate_form( $title, $table, $columns, $values, $skipcols ) {
    $skpcols = [];
    if ( $skipcols !== FALSE ) {
        $skpcols = $skipcols;
    }

    $fks = get_fks( true ); //load list of foreign keys
    $form_html = "<h4>" . $title . "</h4>\n";
    if ( $values !== FALSE ) {
        $form_html .= "<form action='add_entry.php?update' method='post' class='form'>\n";
        if ( isset( $values[ 'ID' ] ) ) {
            $form_html .= "<input type='hidden' name='ID' value='" . $values[ 'ID' ] . "' />\n";
        }
    } else {
        $form_html .= "<form action='add_entry.php' method='post' class='form'>\n";
    }
    $form_html .= "<input type='hidden' name='table' value='" . $table . "' />\n";
    $form_html .= "<table>\n";
    foreach ( $columns as $col ) {
        //Work out the column type
        $col_type = "";
        $col_name = $col[ 'Field' ];

        if ( in_array( $col_name, $skpcols ) ) { //skip this column if in $skpcols
            continue;
        }

        if ( strpos( $col[ 'Type' ], 'int' ) !== false ) {
            $col_type = "number";
        } else {
            $col_type = $col[ 'Type' ];
        }

        $isfk = false;
        if ( ( $values !== FALSE ) and ( $col_name == "ID" ) ) { //edit page
            continue;
        } else {
            $form_html .= "<tr><td>" . $col_name . "</td><td>";
        }
        //echo $col_name . "\n";
        foreach ( $fks as $fk ) {
            if ( $col_name == $fk[ 'column_name' ] ) { //column is a foreign key
                $form_html .= "<select name='" . $col_name . "' class='" . $col_name . "_dd form-control' autocomplete='off'>\n";

                $fk_rows = get_rows( $fk[ 'table_name' ], "" );
                //echo $fk['table_name'] . "\n";
                foreach ( $fk_rows as $fkr ) {
                    //print_r($fkr);
                    //echo "\n";
                    //select the option that matches the value
                    if ( ( $values !== FALSE ) and ( isset( $values[ $col_name ] ) ) and ( $fkr[ 'ID' ] == $values[ $col_name ] ) ) {
                        $form_html .= '<option selected="selected" value="' . $fkr[ 'ID' ] . '">' . $fkr[ 'ID' ] . "</option>\n";
                        echo "success";
                    } else {
                        $form_html .= '<option value="' . $fkr[ 'ID' ] . '">' . $fkr[ 'ID' ] . "</option>\n";
                    }
                }
                $form_html .= "</select>\n";
                $isfk = true;
                break;
            }
        }
        if ( !$isfk ) {
            if ( $col_type == "time" ) {
                $form_html .= "<select name='" . $col_name . "' class='time_dd form-control' autocomplete='off'>\n";
                if ( ( $values !== FALSE ) and ( isset( $values[ $col_name ] ) ) ) {
                    $form_html .= timedd( $values[ $col_name ] );
                } else {
                    $form_html .= timedd( "" );
                }
                $form_html .= "</select>\n";
            } else {
                if ( ( $values !== FALSE ) and ( isset( $values[ $col_name ] ) ) ) {
                    $form_html .= "<input name='" . $col_name . "' type='" . $col_type . "' value='" . $values[ $col_name ] . "' class='form-control' data-validation='" . $col_type . "'/>\n";
                } else {
                    $form_html .= "<input name='" . $col_name . "' type='" . $col_type . "' class='form-control' data-validation='" . $col_type . "'/>\n";
                }
            }
        }

        $form_html .= "</td></tr>\n";

    }
    $form_html .= "</table><input type='submit' value='Submit' class='btn btn-default'>\n</form>\n";
    return $form_html;
}

////  GENERATE TIME DROPDOWN OPTIONS  ////
function timedd( $ts ) {

    $timedd_html = "";
    for ( $t = 8; $t < 18; $t++ ) {
        $t_str = $t . ':';
        if ( $t < 10 ) {
            $t_str = '0' . $t . ':';
        }
        //$mins = ["00", "15", "30", "45"]; 
        for ( $m = 0; $m < 60; $m += 6 ) {
            $time = $t_str . str_pad( $m, 2, "0", STR_PAD_LEFT );;
            //echo $ts . "=" . ($time . ":00"); echo " ";
            if ( ( $time . ":00" ) == $ts ) {
                //echo "Match\n";
                $timedd_html .= "<option selected='selected' value='" . $time . ":00'>" . $time . "</option>";
            } else {
                $timedd_html .= "<option value='" . $time . ":00'>" . $time . "</option>";
            }
        }
        $timedd_html .= "\n";
    }


    return $timedd_html;
}

////  GENERATE TABLE SEARCH FORM  ////
function generate_searchform( $table ) {

    $cols = get_columns( $table, true );
    $fks = get_fks( true );

    $form_html = "<form action='search.php' method='get' class='form form-inline sform'>\n";
    $form_html .= "<input type='hidden' name='t' value='" . $table . "' />\n";

    // DROPDOWN LIST OF COLUMNS //
    $form_html .= "<select name='col' id='searchcolumn' class='form-control' autocomplete='off'>\n";
    foreach ( $cols as $col ) {
        $form_html .= "<option value='" . $col[ 'Field' ] . "'>" . $col[ 'Field' ] . "</option>\n";
    }
    $form_html .= "</select>\n";

    // INPUT FIELD (TEXT,NUMBER,DATE,DROPDOWN) FOR EACH COLUMN
    foreach ( $cols as $col ) {
        //Work out the column type
        $col_type = "";
        $col_name = $col[ 'Field' ];

        if ( strpos( $col[ 'Type' ], 'int' ) !== false ) {
            $col_type = "number";
        } else {
            $col_type = $col[ 'Type' ];
        }

        $isfk = false;

        foreach ( $fks as $fk ) {
            if ( $col_name == $fk[ 'column_name' ] ) { //
                $rows = get_rows( $fk[ 'table_name' ], "" );

                $form_html .= "<select name='" . $col_name . "' class='" . $col_name . "_input sci form-control' autocomplete='off'>\n";
                $form_html .= "<option value='any' selected='selected'>any</option>\n";
                foreach ( $rows as $row ) {
                    $form_html .= '<option value="' . $row[ 'ID' ] . '">' . $row[ 'ID' ] . "</option>\n";
                }
                $form_html .= "</select>\n";
                $isfk = true;
            }
        }
        if ( !$isfk ) {
            $form_html .= "<input name='" . $col_name . "' type='" . $col_type . "' class='" . $col_name . "_input sci form-control' data-validation='" . $col_type . "'/>\n";
        }
    }

    // SEACH BUTTON
    $form_html .= "<div class='searchbtn'><input type='submit' value='-' class='btn btn-default'><img src='img/search.svg'/></div>\n</form>\n";

    return $form_html;
}

function generate_groupbyform( $pid, $table, $group ) {
    $cols = array_diff( get_columns( $table, false ), [ 'ID', 'description', 'activity_comments', 'activity_values', 'minutes', 'time_started', 'time_finished', 'pin', 'type', 'name' ] );
    $gbf_html = "";
    if ( count( $cols ) > 0 ) {
        $gbf_html = "<form action='page.php' method='get' class='form form-inline gbform'>\n";
        $gbf_html .= "<input type='hidden' name='id' value='" . $pid . "' />\n";
        $gbf_html .= "<input type='submit' value='Group By' class='btn btn-default'><select name='g' class='groupby_dd form-control' autocomplete='off'>\n";
        $gbf_html .= "<option value=''>none</option>";
        foreach ( $cols as $col ) {
            if ( $col != "furniture" ) {
                if ( $col == $group ) {
                    $gbf_html .= "<option value='" . $col . "' selected='selected'>" . str_replace( "_", " ", $col ) . "</option>";
                } else {
                    $gbf_html .= "<option value='" . $col . "'>" . str_replace( "_", " ", $col ) . "</option>";
                }
            }
        }
        $gbf_html .= "</select></form>";
    }

    return $gbf_html;
}

////  GENERATE ACTIVITY ADDER PANEL  ////
function addactivitypanel() {
    $panel_html = "<div class='row'>\n";
    $acts = get_rows( "activities", "" );
    foreach ( $acts as $act ) { //get each activity
        $panel_html .= "<div class='col-md-3'>\n<div class='btn btn-default act_btn' id='act_" . $act[ 'ID' ] . "' name='" . $act[ 'ID' ] . "'>\n";
        $panel_html .= $act[ 'name' ];
        $panel_html .= "</div></div>\n";
    }
    $panel_html .= "</div>";
    $panel_html .= "<div class='row act_options'>\n";
    foreach ( $acts as $act ) { //get each activity
        $act_ops = array_filter( explode( ",", $act[ 'options' ] ) ); //get activity options
        $panel_html .= "<div class='col-md-12 act_op' id='actopt_" . $act[ 'ID' ] . "'>\n";
        if ( !empty( $act_ops ) ) { //if this activity has options, add them
            $cb = 0;
            foreach ( $act_ops as $op ) {
                $opvals = explode( "|", $op ); //split options into name and type
                $panel_html .= "<span class='opname'>" . $opvals[ 0 ] . "</span>";

                if ( $opvals[ 1 ] == "bool" ) {
                    $panel_html .= "<div class='chkbx'><input type='checkbox' value='1' name='" . $opvals[ 0 ] . "' id='cb" . $cb . "'/><label for='cb" . $cb . "'><span></span></label></div>";
                }
                $cb++;
            }
        } else {
            //$panel_html .= "-1";
        }
        $panel_html .= "</div>";
    }
    $panel_html .= "</div>";
    $panel_html .= "<textarea class='act_comment'></textarea>\n";
    return $panel_html;
}

////  GENERATE SHIFT ADDER PANEL  ////
function addshiftform( $j_id, $w_id ) {
    $form_html = "<form action='add_entry.php' method='post' class='form' id='shft_add_form'><input type='hidden' name='table' value='shifts' /><input type='hidden' name='job' value='" . $j_id . "' /><input type='hidden' name='worker' value='" . $w_id . "' />";
    $form_html .= "<table><tr><td>Date</td><td><input type='date' name='date' class='form-control' data-validation='date' autocomplete='off' value='" . date( "d-m-Y" ) . "'/></td></tr><tr><td>Time Started</td><td><select name='time_started' class='time_dd form-control' autocomplete='off'>";
    $form_html .= timedd( "" );
    $form_html .= "</select></td></tr><tr><td>Time Finished</td><td><select name='time_finished' class='time_dd form-control' autocomplete='off'>\n";
    $form_html .= timedd( "" );
    $form_html .= "</select></td></tr><tr><td colspan='2'><h3>Activities</h3><div class='shft_acts'>no activities</div></td></tr></table><input type='submit' value='(F)inish Shift' class='btn btn-default shft_finish' ></form>";
    return $form_html;
}

////  GENERATE JS FOR LIST OF ACTIVITIES AND THEIR OPTIONS  ////
function loadactivitiesjs() {
    $ac_js = "<script>";
    $ac_js .= "acts_list = [];\n";
    $acts = get_rows( "activities", "" );
    foreach ( $acts as $act ) { //get each activity
        $act_ops = array_filter( explode( ",", $act[ 'options' ] ) ); //get activity options

        if ( !empty( $act_ops ) ) { //if this activity has options, add them
            $ac_js .= 'acts_list[' . $act[ 'ID' ] . '] = {name:"' . $act[ 'name' ] . '", options:{';
            $firstop = true;
            foreach ( $act_ops as $op ) {
                $opvals = explode( "|", $op ); //split options into name and type
                if ( !$firstop ) {
                    $ac_js .= ',';
                }
                $firstop = false;
                $ac_js .= '"' . $opvals[ 0 ] . '":"' . $opvals[ 1 ] . '"';
            }
            $ac_js .= '}};';
        } else {
            $ac_js .= 'acts_list[' . $act[ 'ID' ] . '] = {name:"' . $act[ 'name' ] . '", options:-1};';
        }

        $ac_js .= "\n";
    }
    $ac_js .= "console.log(acts_list);</script>";

    return $ac_js;
}

//// HEADER FOR DETAIL PAGES
function getdetailpageheader( $prepageurl, $prepagename, $title ) {
    echo '<a href="' . $prepageurl . '" class="page-header-breadcrumb"><div class="btn btn-default">◀ &nbsp; ' . $prepagename . '</div></a>';
    $redirecturl = ph_script_filename() . "?" . $_SERVER[ 'QUERY_STRING' ];
    if ( isset( $_GET[ 'add' ] ) ) { //add a new worker
        $redirecturl = $prepageurl;
        echo "<h2>Add " . $title . "</h2><div class='panel panel-default' style='position: relative'>";
    } else {
        echo "<h2>" . $title . " Details</h2><div class='panel panel-default' style='position: relative'><input type='button' id='editbtn' value='Edit' class='btn btn-default'/><input type='button' id='cancelbtn' value='Cancel' class='btn btn-default'/>";
    }
    return $redirecturl;
}

//// FOOTER FOR DETAIL PAGES
function getdetailpagefooter( $formid, $table, $redirecturl ) {
    echo "</div><script>";
    if ( !empty( $redirecturl ) )
        echo '
        redirecturl = "' . $redirecturl . '";
        pagefunctions();
        ';
    echo "detailpagefunctions('" . $formid . "','" . $table . "');</script>";
    include 'include/footer.php';
}

function roundTime( $timestamp, $updown = 0 ) {
    $precision = 6;
    $timestamp = strtotime( $timestamp );
    $precision = 60 * $precision;
    if ( $updown == 1 ) {
        return date( 'H:i:s', ceil( $timestamp / $precision ) * $precision );
    } else if ( $updown == -1 ) {
        return date( 'H:i:s', floor( $timestamp / $precision ) * $precision );
    } else {
        return date( 'H:i:s', round( $timestamp / $precision ) * $precision );
    }
}


function ph_format_shim_insert( $value, $order_of_magnitude = false ) {
    $shim = '';
    if ( $order_of_magnitude ) {
        $number_of_shims = $order_of_magnitude - max( 0, floor( log10( $value ) ) );
        for ( $i = 0; $i < $number_of_shims; $i++ )
            $shim .= '&#x2007;';
    }
    return $shim;
}

/**
 * Convert minutes to hours:minutes
 *
 * @param $minutes
 * @return string
 */
function ph_format_hours_minutes( $minutes, $order_of_magnitude = false ) {
    if ( is_numeric( $minutes ) ) {
        if ( $minutes == 0 )
            return ph_format_shim_insert( 0, $order_of_magnitude ) . '-';
        $hours = floor( $minutes / 60 );
        $hours_and_minutes = ph_format_shim_insert( $hours, $order_of_magnitude ) . $hours . ":" . str_pad( ( $minutes % 60 ), 2, "0", STR_PAD_LEFT );
        return $hours_and_minutes;
    }
    return $minutes;
}

function ph_format_currency( $value, $order_of_magnitude = false ) {
    if ( is_numeric( $value ) ) {
        $formatted_value = ph_format_shim_insert( $value, $order_of_magnitude ) . '$' . number_format( $value, 2 );
        return $formatted_value;
    }
    return $value;

}

function ph_format_percentage( $value, $for_table = false ) {
    if ( is_numeric( $value ) ) {
        if ( $value == 0 )
            return '-';
        $formatted_value = number_format( 100 * $value, 1 ) . '%';
        if ( $for_table && $value < 0.1 ) $formatted_value = '&#x2007;' . $formatted_value;
        return $formatted_value;
    }
    return $value;
}

function ph_format_table_value( $table_array, $columns ) {
    foreach ( $columns as $column => $column_data ) {
        $max_value = 0;
        $output_key = !empty( $column_data[ 'output_column' ] ) ? $column_data[ 'output_column' ] : $column;
        foreach ( $table_array as $key => $array ) {
            if ( !is_numeric( $table_array[ $key ][ $column ] ) ) {
                ph_messages()->add_message( 'Non numeric value "<strong>' . $table_array[ $key ][ $column ] . '</strong>" in column "<strong>' .  $column . '</strong>" sent to format function. You probably already formatted the value mistakenly' );
                return $table_array;
                break;
            }
            $max_value = max( $max_value, $table_array[ $key ][ $column ] );
        }
        switch ( $column_data[ 'type' ] ) {
            case 'currency' :
                $max_order_of_magnitude = max( 0, floor( log10( $max_value ) ) );
                foreach ( $table_array as $key => &$row ) {
                    $row[ $output_key ] = ph_format_currency( $row[ $column ], $max_order_of_magnitude );
                }
                break;
            case 'hoursminutes':
                $max_order_of_magnitude = max( 0, floor( log10( $max_value / 60 ) ) );
                foreach ( $table_array as $key => &$row ) {
                    $row[ $output_key ] = ph_format_hours_minutes( $row[ $column ], $max_order_of_magnitude );
                }
                break;
            case 'percentage':
                $for_table = $max_value >= 0.1 ? true : false;
                foreach ( $table_array as $key => &$row ) {
                    $row[ $output_key ] = ph_format_percentage( $row[ $column ], $for_table );
                }
        }
    }
    return $table_array;
}


function ph_get_template_part( $template_name, $args = array() ) {
    if ( !empty( $args ) && is_array( $args ) ) {
        extract( $args );
    }
    $path = 'templates/' . $template_name . '.php';
    if ( !file_exists( $path ) ) {
        echo printf( '%s does not exist.', '<code>' . $path . '</code>' );
        return;
    }
    include $path;
}