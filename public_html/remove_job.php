<?php include 'include/crm_init.php';


if ( !isset( $_GET[ 'id' ] ) ) {
    header( "Location: index.php" );
} else {
    $id = ph_validate_number( $_GET[ 'id' ] );

    $sql = "DELETE FROM jobs WHERE ID = " . $id;
    $con = init_crmdb();
    $qry = mysqli_query( $con, $sql );
    if ( $qry ) { ?>

        <div class='panel panel-default'>
            <center>
                <h1>Job <?php echo $_GET[ 'id' ]; ?> deleted</h1>
                <br></center>
        </div>
        <?php
    } else {
        $stre = "<b>Error: " . $sql . "<br>" . mysqli_error( $con ) . "</b>";
        close_crmdb( $con );
        return $stre;
    }


}
?>
    <script>
        pagefunctions();
        setTimeout( function () {
            location.href = 'page.php?id=3';
        }, 600 );
    </script>

<?php include 'include/footer.php'; ?>