<?php

namespace Phoenix;

include '../src/crm_init.php';

$jobID = ph_validate_number($_GET['job_id']);
$activityID = ph_validate_number($_GET['activity_id']);
?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container">
                <h1>Description of Work</h1>
                <div style="text-align: center;">
                    <form action="nextshift.php" class="detailform" method="get">
                        <input type="hidden" name="job_id" value="<?php echo $jobID; ?>">
                        <input type="hidden" name="activity_id" value="<?php echo $activityID; ?>">
                        <input type="text" class='form-control' name="comment" value="" autocomplete="off" autofocus>
                        <br>
                        <input type="submit" class='btn btn-default' value="Done">
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php ph_get_template_part('footer') ?>