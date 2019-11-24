<?php

namespace Phoenix;

include '../src/crm_init.php'; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container actsbtns">
                <h1>Choose Job</h1>

                <?php

                $query = "SELECT jobs.ID, jobs.date_started, jobs.date_finished, jobs.status, jobs.priority, jobs.customer, jobs.furniture, jobs.description, customers.name 
as customer FROM jobs INNER JOIN customers ON jobs.customer=customers.ID WHERE jobs.status = 'jobstat_red' AND jobs.ID != 0";
                $jobRows = PDOWrap::instance()->run( $query );


                foreach ( $jobRows as $jobRow ) {
                    ?>
                    <div class="row cjob">
                        <div class="col-md-8">
                                    <span><b><?php echo $jobRow['customer']; ?></b><br>Job No. <?php echo $jobRow['ID']; ?>
                                        &nbsp;&nbsp;&nbsp;<?php echo $jobRow['description']; ?></span></div>
                        <div class="col-md-4">
                            <a href="choosefur.php?job_id=<?php echo $jobRow['ID']; ?>"
                               class="btn btn-default">Select</a>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div class="row cjob">
                    <div class="col-md-12">
                        <span><b>Factory</b><br></span>
                        <a href="chooseactivity.php?job_id=0" class="btn btn-default">Select</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php ph_get_template_part( 'footer' ); ?>