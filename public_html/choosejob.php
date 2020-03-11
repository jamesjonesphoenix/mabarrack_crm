<?php

namespace Phoenix;
//27 jobs
include '../src/crm_init.php';

$jobFactory = new JobFactory( PDOWrap::instance(), Messages::instance() );
$activeJobs = $jobFactory->getActiveJobs();
krsort( $activeJobs );
$lastWorkedJob = $jobFactory->getLastWorkedJob( CurrentUser::instance()->id );


?>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container">
                <h1>Choose Job</h1>
                <?php
                if ( $lastWorkedJob !== null && $lastWorkedJob->id !== 0 ) {
                    echo '<h2>Last Worked Job</h2>';
                    getTemplatePart( 'choose-job-fragment', ['job' => $lastWorkedJob] ); ?>
                    <?php
                }
                ?>
                <h2>Factory</h2>
                <div class="row cjob">
                    <div class="col-md-12">
                        <span><b>Factory</b><br></span>
                        <a href="chooseactivity.php?job_id=0" class="btn btn-default">Select</a>
                    </div>
                </div>
                <h2>Active Jobs</h2>
                <?php foreach ( $activeJobs as $job ) {
                    getTemplatePart( 'choose-job-fragment', ['job' => $job] );
                } ?>
            </div>
        </div>
    </div>

    <?php getTemplatePart( 'footer' ); ?>