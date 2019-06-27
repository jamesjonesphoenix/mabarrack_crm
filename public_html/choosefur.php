<?php include 'include/crm_init.php'; ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container actsbtns">
                <h1>Choose Furniture</h1>

                <?php
                $jid = ph_validate_number( $_GET[ 'jid' ] );

                $j_row = get_rows( "jobs", "WHERE ID = " . $jid )[ 0 ];

                $fjson = json_decode( $j_row[ 'furniture' ], true );

                $findex = 0;
                foreach ( $fjson as $ff ) {

                    $ffid = current( array_keys( $ff ) );
                    $ffq = reset( $ff );
                    $ffn = get_rows( "furniture", "WHERE ID = " . $ffid )[ 0 ][ 'name' ];

                    ?>
                    <div class="row cjob">
                        <div class="col-md-12">
                            <span><b><?php echo $ffn; ?></b><br>Quantity: <?php echo $ffq; ?></span>
                            <a href="chooseact.php?jid=<?php echo $_GET[ 'jid' ]; ?>&fid=<?php echo $findex; ?>"
                               class="btn btn-default">Select</a>
                        </div>
                    </div>
                    <?php
                    $findex++;
                }


                ?>

            </div>
        </div>
    </div>

<?php include 'include/footer.php'; ?>