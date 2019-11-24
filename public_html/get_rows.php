<?php include '../src/crm_init.php';

//$rows = \Phoenix\PDOWrap::instance()->getRows( $_POST[ 'table' ], $_POST[ 'query' ] ); //comment out for security

mysqli_close( $conn );

if ( !$rows ) {
    echo 'null';
} else {
    echo json_encode( $rows );
}