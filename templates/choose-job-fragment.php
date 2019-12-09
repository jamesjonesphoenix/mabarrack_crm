<?php if ( !empty( $job ) ) { ?>
    <div class="row cjob">
        <div class="col-md-8">
            <span><b><?php echo $job->customer->name; ?></b><br>Job No. <?php echo $job->id; ?>&nbsp;&nbsp;&nbsp;<?php echo $job->description; ?>
            </span>
        </div>
        <div class="col-md-4">
            <a href="choosefur.php?job_id=<?php echo $job->id; ?>" class="btn btn-default">Select</a>
        </div>
    </div>
<?php } ?>
