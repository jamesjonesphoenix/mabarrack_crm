<?php

namespace Phoenix;

/**
 *
 */
function ph_sec_session_start()
{
    $session_name = 'sec_session_id';   // Set a custom session name
    $secure = USING_SSL;
    // This stops JavaScript being able to access the session id.
    $httponly = true;
    // Forces sessions to only use cookies.
    if ( ini_set( 'session.use_only_cookies', 1 ) === false ) {
        ph_redirect( 'login', array('message' => 'session_ini_failed') );
        exit();
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params( 43200, $cookieParams['path'], $cookieParams['domain'], $secure, $httponly );
    // Sets the session name to the one set above.
    session_name( $session_name );

    session_start();            // Start the PHP session

    if ( !empty( $_SESSION['LAST_ACTIVITY'] ) && (time() - $_SESSION['LAST_ACTIVITY'] > 28800) ) {
        // last request was more than 8 hours ago
        session_unset();     // unset $_SESSION variable for the run-time
        session_destroy();   // destroy session data in storage
        ph_redirect( 'login', array('message' => 'login_timed_out') );
    } else {

        $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

        if ( empty( $_SESSION['CREATED'] ) ) {
            $_SESSION['CREATED'] = time();
        } else if ( !defined( 'DOING_AJAX' ) && time() - $_SESSION['CREATED'] > 1800 ) {
            // session started more than 30 minutes ago
            session_regenerate_id();    // change session ID for the current session and invalidate old session ID
            $_SESSION['CREATED'] = time();  // update creation time
        }
    }
    //session_regenerate_id();    // regenerated the session, delete the old one.
}

/**
 * @param string $location
 * @param array|string $args
 * @return bool
 */
function ph_redirect(string $location = '', $args = [])
{
    if ( empty( $location ) ) {
        return false;
    }
    $argString = '';
    if ( !empty( $args ) ) {
        $argString = '?';
        if ( is_array( $args ) ) {

            $num_args = count( $args );
            $i = 0;
            foreach ( $args as $arg_name => $arg_value ) {
                $argString .= $arg_name . '=' . $arg_value;
                if ( ++$i !== $num_args ) {
                    $argString .= '&';
                }
            }
        } elseif ( is_string( $args ) ) {
            $argString .= $args;
        }
    }

    //clean location variable
    $location = ph_sanitize_redirect( $location );
    if ( strpos( $location, '.php' ) === false ) {
        $location .= '.php';
    }

    //redirect
    header( 'Location: ' . $location . $argString, true, 302 );
    exit();
}

/**
 * Taken verbatim from WordPress wp_sanitize_redirect() in pluggable.php
 *
 * Sanitizes a URL for use in a redirect.
 *
 * @param string $location The path to redirect to.
 * @return string Redirect-sanitized URL.
 **@since 2.3.0
 *
 */
function ph_sanitize_redirect($location)
{
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
    $strip = array('%0d', '%0a', '%0D', '%0A');
    return _ph_deep_replace( $strip, $location );
}

/**
 * Taken verbatim from WordPress _wp_sanitize_utf8_in_redirect() in pluggable.php
 *
 * URL encode UTF-8 characters in a URL.
 *
 * @param array $matches RegEx matches against the redirect location.
 * @return string URL-encoded version of the first RegEx match.
 * @see wp_sanitize_redirect()
 *
 * @ignore
 * @since 4.2.0
 * @access private
 *
 */
function _ph_sanitize_utf8_in_redirect($matches)
{
    return urlencode( $matches[0] );
}

/**
 * Taken Verbatim from WordPress wp_kses_no_null() in kses.php
 *
 * Removes any invalid control characters in $string.
 *
 * Also removes any instance of the '\0' string.
 *
 * @param string $string
 * @param array $options Set 'slash_zero' => 'keep' when '\0' is allowed. Default is 'remove'.
 * @return string
 * @since 1.0.0
 *
 */
function ph_kses_no_null($string, $options = null)
{
    if ( !isset( $options['slash_zero'] ) ) {
        $options = array('slash_zero' => 'remove');
    }

    $string = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string );
    if ( 'remove' === $options['slash_zero'] ) {
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
 * @param string|array $search The value being searched for, otherwise known as the needle.
 *                              An array may be used to designate multiple needles.
 * @param string $subject The string being searched and replaced on, otherwise known as the haystack.
 * @return string The string with the replaced svalues.
 * @since 2.8.1
 * @access private
 *
 */
function _ph_deep_replace($search, $subject)
{
    $subject = (string)$subject;

    $count = 1;
    while ( $count ) {
        $subject = str_replace( $search, '', $subject, $count );
    }

    return $subject;
}

/**
 * @param $number
 * @return bool|int
 */
function ph_validate_number($number)
{
    $number = (int)$number;
    if ( !is_numeric( $number ) ) {
        return false;
    }
    if ( !is_int( $number ) ) {
        return false;
    }
    if ( $number < 1 ) {
        return 0;
    }
    return $number;
}

/**
 * @param bool $suffix
 * @return string
 */
function ph_script_filename($suffix = false)
{
    return basename( $_SERVER['SCRIPT_FILENAME'], $suffix );
}

/**
 * RETURN NAME OF DETAIL PAGE FOR A TABLE
 *
 * @param $table
 * @return bool
 */
function getDetailPage($table)
{
    $detailPages = array(
        'jobs' => 'job.php',
        'shifts' => 'shift.php',
        'users' => 'worker.php',
        'customers' => 'customer.php',
        'furniture' => 'furniture.php'
    );
    return $detailPages[$table] ?? '';
}


/**
 * @param $query
 * @param $queryArgs
 * @return array|bool
 */
function getColumnsQuery($query, $queryArgs)
{

    $query = PDOWrap::instance()->getRow( 'qrys', array('name' => $query) )['query'];

    foreach ( $queryArgs as $argName => $argValue ) {
        $query = str_replace( 'arg' . $argName, $argValue, $query );
    }
    return array_keys( PDOWrap::instance()->run( $query )->fetch() ) ?? [];
}

/**
 * RETURN ARRAY OF ROWS FROM CUSTOM QUERY
 *
 * @param $query
 * @param $queryArgs
 * @return array|bool
 */
function getRowsQuery($query, $queryArgs)
{
    $query = PDOWrap::instance()->getRow( 'qrys', array('name' => $query) )['query'];
    foreach ( $queryArgs as $argName => $argValue ) {
        $query = str_replace( 'arg' . $argName, $argValue, $query );
    }
    return PDOWrap::instance()->run( $query )->fetchAll() ?? [];
}

/**
 * GENERATE TABLES FROM $columns AND $ROWS
 *
 * @param $columns
 * @param $rows
 * @param string $viewButtonArgs table name
 * @return string
 */
function generateTable($columns, $rows, $viewButtonArgs = '')
{
    $html = "<table class='tablesorter table table-hover'><thead><tr>";
    $first = true;
    $hasStatus = false;
    $colidx = 0;
    foreach ( $columns as $column ) {
        if ( $column !== 'status' ) {
            if ( in_array( $column, ['ID', 'priority'] ) ) {
                $class = 'w50';
            } else if ( $colidx === (count( $columns ) - 1) ) {
                $class = 'desctd';
            } else if ( in_array( $column, ['worker', 'name'] ) ) {
                $class = 'w200';
            } else {
                $class = 'w100';
            }
            $html .= '<th class="' . $class . '"  >' . str_replace( '_', ' ', $column ) . '</th>';
        } else {
            $hasStatus = true;
        }
        $colidx++;
    }

    if ( !empty( $viewButtonArgs ) && CurrentUser::instance()->role === 'admin' ) {
        if ( !is_array( $viewButtonArgs ) ) {
            $viewButtonArgs = array(
                array('table' => $viewButtonArgs, 'detail_page' => getDetailPage( $viewButtonArgs )));
        }

        foreach ( $viewButtonArgs as $key => &$view_button ) {
            $view_button['detail_page'] = getDetailPage( $view_button['table'] );
            if ( (!empty( $view_button['detail_page'] )) ) {
                $html .= "<td class='viewth'></td>";
            }
            if ( empty( $view_button['column'] ) ) {
                $view_button['column'] = 'ID';
            }
            if ( empty( $view_button['text'] ) ) {
                $view_button['text'] = 'View';
                $view_button['text'] .= ' ' . rtrim( $view_button['table'], 's' );
            }
        }
    }

    $html .= '</tr></thead>';
    $html .= '<tbody>';

    if ( $rows === false ) {
        $html .= '    <tr><td class="noresult" colspan="' . (count( $columns ) + 1) . '">No results found</td></tr>';
    } else {
        foreach ( $rows as $row ) {

            if ( $hasStatus ) {
                $html .= "    <tr class='" . $row['status'] . "'>";
            } else {
                $html .= '    <tr>';
            }
            foreach ( $columns as $column ) {
                //if ( empty( $row[ $column ] ) )
                //  $row[ $column ] = '';

                switch( $column ) {
                    case 'status':
                        break;
                    case 'job':
                        if ( is_numeric( $row[$column] ) ) {
                            $jobID = $row[$column];
                        } else {
                            $jobID = !empty( $row[$column]->id ) || $row[$column]->id === 0 ? $row[$column]->id : $row[$column];
                        }
                        if ( $jobID === 0 ) {
                            $row[$column] = 'Factory';
                        } else {
                            $row[$column] = $jobID;
                        }


                        break;
                    case 'furniture':
                        $rowString = '';
                        if ( is_string( $row[$column] ) ) {
                            $rowJSON = json_decode( $row[$column], true );
                            foreach ( $rowJSON as $ff ) {
                                $furnitureID = current( array_keys( $ff ) );
                                $ffq = reset( $ff );
                                $furnitureName = PDOWrap::instance()->getRow( 'furniture', array('ID' => $furnitureID) )['name'];
                                $rowString .= $ffq . ' ' . ucfirst( $furnitureName ) . ($ffq > 1 ? 's<br>' : '<br>');
                            }
                        } else {
                            foreach ( $row[$column] as $furniture ) {
                                $rowString .= $furniture->getFurnitureString() . '<br>';
                            }
                        }
                        $row[$column] = $rowString;
                        break;
                    case 'cost':
                    case 'price':
                    case 'rate':
                    case 'material_cost':
                    case 'sale_price':
                    case 'contractor_cost':
                    case 'spare_cost':
                        $row[$column] = Format::currency( $row[$column] );
                        break;
                    case 'time':
                    case 'time_started':
                    case 'time_finished':
                        if ( !empty( strtotime( $row[$column] ) ) ) {
                            $row[$column] = substr_replace( $row[$column], '', -3 );
                        }
                        break;
                    case 'date':
                    case 'date_started':
                    case 'date_finished':
                        if ( !DateTime::validateDate( $row[$column] ) ) {
                            $row[$column] = 'N/A';
                        } else {
                            $row[$column] = date( 'd-m-Y', strtotime( $row[$column] ) );
                        }

                        //$row[ $column ] = '<span class="hidden-date">' . $row[ $column ] . '</span>' .  date( "d-m-Y", strtotime( $row[ $column ] ) );
                        break;
                }

                if ( $column !== 'status' ) {
                    $html .= "<td class='" . $column . "'>" . $row[$column] . '</td>';
                }
            }
            if ( !empty( $viewButtonArgs ) && CurrentUser::instance()->role === 'admin' ) {
                foreach ( $viewButtonArgs as $key => &$view_button ) {
                    //print_r('"'.$row[ $view_button[ 'column' ] ] . '"<br>');
                    if ( !empty( $view_button['detail_page'] ) && !empty( $row[$view_button['column']] ) && $row[$view_button['column']] !== '0' ) {
                        $html .= '<td class="w50"><a href="' . $view_button['detail_page'] . '?id=' . $row[$view_button['column']] . '"><div class="btn btn-default viewbtn">' . $view_button['text'] . '</div></a></td>';
                    } else {
                        $html .= '<td>-</td>';
                    }

                }
            }
            $html .= '</tr></a>';
        }
    }
    $html .= '</tbody></table>';
    return $html;
}

/**
 * RETURN HTML OF TABLE
 *
 * @param $table
 * @return bool|string
 */
function getTable($table)
{
    if ( !PDOWrap::instance()->tableExists( $table ) ) {
        return "table '" . $table . "' not found<br><br>";;
    }
    $rows = PDOWrap::instance()->getRows( $table );
    if ( !$rows ) {
        return false;
    }
    return generateTable( array_keys( $rows[0] ), $rows, $table );
}


/**
 * @param $pid
 * @param $table
 * @param $group
 * @return string
 */
function generateGroupByForm($pid, $table, $group)
{
    $columns = array_diff(
        PDOWrap::instance()->getColumns( $table ), ['ID', 'description', 'activity_comments', 'activity_values', 'minutes', 'time_started', 'time_finished', 'pin', 'type', 'name', 'password']
    );
    if ( count( $columns ) > 0 ) {
        ?>
        <form action='page.php' method='get' class='form form-inline gbform'>
            <input type='hidden' name='id' value='<?php echo $pid; ?>'/>
            <input type='submit' value='Group By' class='btn btn-default'><select name='g'
                                                                                  class='groupby_dd form-control'
                                                                                  autocomplete='off'>
                <option value=''>none</option>
                <?php
                foreach ( $columns as $column ) {
                    if ( $column !== 'furniture' ) {
                        $selected = $column == $group ? ' selected="selected"' : '';
                        echo sprintf( '<option value="' . $column . '"%s>' . str_replace( '_', ' ', $column ) . '</option>', $selected );
                    }
                }
                ?>
            </select>
        </form>
        <?php
    }
    //return $gbf_html;
}

/**
 * GENERATE ACTIVITY ADDER PANEL
 *
 * @return string
 */
function addActivityPanel()
{
    $panel_html = "<div class='row'>";
    $activities = PDOWrap::instance()->getRows( 'activities' );
    foreach ( $activities as $activity ) { //get each activity
        $panel_html .= "<div class='col-md-3'><div class='btn btn-default act_btn' id='act_" . $activity['ID'] . "' name='" . $activity['ID'] . "'>";
        $panel_html .= $activity['name'];
        $panel_html .= "</div></div>";
    }
    $panel_html .= '</div>';
    $panel_html .= "<div class='row act_options'>";
    foreach ( $activities as $activity ) { //get each activity
        $activityOptions = array_filter( explode( ',', $activity['options'] ) ); //get activity options
        $panel_html .= "<div class='col-md-12 act_op' id='actopt_" . $activity['ID'] . "'>";
        if ( !empty( $activityOptions ) ) { //if this activity has options, add them
            $cb = 0;
            foreach ( $activityOptions as $op ) {
                $opvals = explode( '|', $op ); //split options into name and type
                $panel_html .= "<span class='opname'>" . $opvals[0] . '</span>';

                if ( $opvals[1] === 'bool' ) {
                    $panel_html .= "<div class='chkbx'><input type='checkbox' value='1' name='" . $opvals[0] . "' id='cb" . $cb . "'/><label for='cb" . $cb . "'><span></span></label></div>";
                }
                $cb++;
            }
        }
        $panel_html .= '</div>';
    }
    $panel_html .= '</div>';
    $panel_html .= "<textarea class='act_comment'></textarea>";
    return $panel_html;
}

/**
 * GENERATE SHIFT ADDER PANEL
 *
 * @param $jobID
 * @param $workerID
 * @return string
 */
function addShiftForm($jobID, $workerID)
{

    return "<form action='add_entry.php' method='post' class='form' id='shft_add_form'><input type='hidden' name='table' value='shifts' /><input type='hidden' name='job' value='"
        . $jobID
        . "' /><input type='hidden' name='worker' value='"
        . $workerID
        . "' /><table><tr><td>Date</td><td><input type='date' name='date' class='form-control' data-validation='date' autocomplete='off' value='"
        . date( 'd-m-Y' )
        . "'/></td></tr><tr><td>Time Started</td><td><select name='time_started' class='time_dd form-control' autocomplete='off'>"
        . timeDropDown( '' )
        . "</select></td></tr><tr><td>Time Finished</td><td><select name='time_finished' class='time_dd form-control' autocomplete='off'>"
        . timeDropDown( '' ) .
        "</select></td></tr><tr><td colspan='2'><h3>Activities</h3><div class='shft_acts'>no activities</div></td></tr></table><input type='submit' value='(F)inish Shift' class='btn btn-default shift-finish' ></form>";
}

/**
 * HEADER FOR DETAIL PAGES
 *
 * @param $prepageurl
 * @param $prepagename
 * @param $title
 * @return string
 */
function getDetailPageHeader($prepageurl, $prepagename, $title)
{
    echo '<a href="' . $prepageurl . '" class="page-header-breadcrumb"><div class="btn btn-default">◀ &nbsp; ' . $prepagename . '</div></a>';
    $redirectURL = ph_script_filename() . '?' . $_SERVER['QUERY_STRING'];
    if ( isset( $_GET['add'] ) ) { //add a new worker
        $redirectURL = $prepageurl;
        echo '<h2>Add ' . $title . "</h2><div class='panel panel-default' style='position: relative'>";
    } else {
        echo '<h2>' . $title . " Details</h2>
<div class='panel panel-default' style='position: relative'>
<input type='button' id='edit-button' value='Edit' class='btn btn-default'/>
<input type='button' id='cancel-button' value='Cancel' class='btn btn-default'/>";
    }
    return $redirectURL;
}

/**
 * FOOTER FOR DETAIL PAGES
 *
 * @param $formid
 * @param $table
 * @param $redirectURL
 */
function getDetailPageFooter($formid, $table, $redirectURL)
{
    ?>
    <script>
    <?php
    if ( !empty( $redirectURL ) ) {
        /*
        echo '
        redirectURL = "' . $redirectURL . '";
        pageFunctions();
        ';
        */
        echo 'pageFunctions();';
    }
    echo "detailPageFunctions('" . $formid . "','" . $table . "');</script>";
    getTemplatePart('footer' );
}

/**
 * @param $template_name
 * @param array $args
 */
function getTemplatePart($template_name, $args = array())
{
    if ( !empty( $args ) && is_array( $args ) ) {
        extract( $args, null );
    }
    $path = '../templates/' . $template_name . '.php';
    if ( !file_exists( $path ) ) {
        echo printf( '%s does not exist.', '<code>' . $path . '</code>' );
        return;
    }
    include $path;
}

/**
 * @param array $options
 * @param string $name
 * @param string $selected
 */
function ph_generateOptionSelect(array $options = [], string $name = '', $selected = '')
{

    echo '<select class="form-control viewinput w300" name="' . $name . '" autocomplete="off">';
    foreach ( $options as $option ) {

        $selectedAttribute = !empty( $selected ) && $option['value'] === $selected ? ' selected="selected"' : '';
        echo '<option value="' . $option['value'] . '"' . $selectedAttribute . '>' . $option['display'] . '</option>';
    }
    echo '</select>';
}

/**
 * Main instance of ph_Messages.
 *
 * Returns the main instance of WC to prevent the need to use globals.
 *
 * @param bool $ph_user
 * @return Messages
 */
function ph_messages($ph_user = false)
{
    return Messages::instance( $ph_user );
}

/**
 * GENERATE TIME DROPDOWN OPTIONS
 * Probably not needed as we now directly enter time.
 *
 * @param $ts
 * @return string
 */
/*
function timeDropDown($ts)
{

    $html = '';
    for ( $t = 8; $t < 18; $t++ ) {
        $t_str = $t . ':';
        if ( $t < 10 ) {
            $t_str = '0' . $t . ':';
        }
        //$mins = ["00", "15", "30", "45"];
        for ( $m = 0; $m < 60; $m += 6 ) {
            $time = $t_str . str_pad( $m, 2, '0', STR_PAD_LEFT );
            $selected = ($time . ':00') === $ts ? ' selected="selected"' : '';

            $html .= '<option' . $selected . ' value="' . $time . ':00">' . $time . '</option>';
        }
    }


    return $html;
}
*/