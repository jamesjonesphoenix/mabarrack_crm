<?php

namespace Phoenix;

include '../src/crm_init.php';

$jobID = ph_validate_number( $_GET['job_id'] );
$jobFactory = new JobFactory( PDOWrap::instance(), Messages::instance() );
$job = $jobFactory->getJob( $jobID );

?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container">
                <h1>Choose Furniture</h1>
                <?php
                if ( count( $job->furniture ) > 0 ) {
                    foreach ( $job->furniture as $furniture ) {
                        getTemplatePart( 'choose-furniture-fragment', [
                            'name' => $furniture->name,
                            'quantity' => $furniture->quantity,
                            'id' => $furniture->id,
                            'job_id' => $_GET['job_id'],
                        ] );
                    }
                } else {
                    getTemplatePart( 'choose-furniture-fragment', [
                        'name' => 'This Job Has No Furniture',
                        'quantity' => 0,
                        'id' => 0,
                        'job_id' => $_GET['job_id'],
                    ] );
                } ?>
            </div>
        </div>
    </div>
    <?php getTemplatePart( 'footer' ); ?>