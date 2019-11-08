<?php include '../src/crm_init.php';

//$rows = get_rows( $conn, $_POST[ 'table' ], $_POST[ 'query' ] ); //comment out for security

mysqli_close( $conn );

if ( !$rows ) {
    echo 'null';
} else {
    echo json_encode( $rows );
}