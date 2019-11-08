<?php

namespace Phoenix;

include '../src/crm_init.php'; ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container actsbtns">
                <h1>Choose Furniture</h1>

                <?php
                $jobID = ph_validate_number( $_GET['job_id'] );

                $jobRow = PDOWrap::instance()->getRow( 'jobs', array('ID' => $jobID) );

                $furnitureJSON = json_decode( $jobRow['furniture'], true );

                $furnitureID = 0;
                
                foreach ( $furnitureJSON as $furniture ) {
                    $furnitureID = current( array_keys( $furniture ) );
                    $furnitureQuantity = reset( $furniture );
                    
                    $furnitureName = PDOWrap::instance()->getRow( 'furniture', array('ID' => $furnitureID) )['name'];
                    ?>
                    <div class="row cjob">
                        <div class="col-md-12">
                            <span><b><?php echo $furnitureName; ?></b><br>Quantity: <?php echo $furnitureQuantity; ?></span>
                            <a href="chooseactivity.php?job_id=<?php echo $_GET['job_id']; ?>&furniture_id=<?php echo $furnitureID; ?>"
                               class="btn btn-default">Select</a>
                        </div>
                    </div>
                    <?php
                    $furnitureID++;
                }
                ?>

            </div>
        </div>
    </div>

    <?php ph_get_template_part( 'footer' ); ?>