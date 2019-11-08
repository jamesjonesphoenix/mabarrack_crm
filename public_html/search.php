<?php include '../src/crm_init.php';


////  SETUP SEARCH VARIABLES  ////
$s_table = ''; //search table
$s_col = ''; //search column
$s_value = ''; //search value

////  LOAD URL PARAMETERS  ////
if ( isset( $_GET[ 't' ] ) ) {
    $s_table = $_GET[ 't' ];
} //check if table given
if ( isset( $_GET[ 'col' ] ) ) {
    $s_col = $_GET[ 'col' ];
    if ( isset( $_GET[ $s_col ] ) ) {
        $s_value = $_GET[ $s_col ];
    } else {
        echo 'invalid search value';
    }
}

echo "Search the table '" . $s_table . "' where " . $s_col . ' = ' . $s_value;
if ( $s_value !== 'any') {
    echo "<script>setTimeout(function() {location.href = 'page.php?t=" . $s_table . '&q=tqry&qa=' . $s_col . ',' . $s_value . "';},1000);</script>";
} else {
    echo "<script>setTimeout(function() {location.href = 'page.php?t=" . $s_table . "';},1000);</script>";
}