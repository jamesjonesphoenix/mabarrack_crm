<?php include 'include/crm_init.php'; ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container actsbtns">
                <h1>Choose Job</h1>

                <?php

                $job_rows = get_rows_qry( "jip", [] );

                foreach ( $job_rows as $job_row ) {
                    ?>
                    <div class="row cjob">
                        <div class="col-md-8">
                                    <span><b><?php echo $job_row[ 'customer' ]; ?></b><br>Job No. <?php echo $job_row[ 'ID' ]; ?>
                                        &nbsp;&nbsp;&nbsp;<?php echo $job_row[ 'description' ]; ?></span></div>
                        <div class="col-md-4">
                            <a href="choosefur.php?job_id=<?php echo $job_row[ 'ID' ]; ?>"
                               class="btn btn-default">Select</a>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div class="row cjob">
                    <div class="col-md-12">
                        <span><b>Factory</b><br></span>
                        <a href="chooseact.php?job_id=0" class="btn btn-default">Select</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'include/footer.php'; ?>