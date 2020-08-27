<?php

namespace Phoenix;

include '../src/crm_init.php';

$jobID = phValidateID($_GET['job_id']);
$activityID = phValidateID($_GET['activity_id']);
?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container">
                <h1>Description of Work</h1>
                <div style="text-align: center;">
                    <form action="nextshift.php" class="detail-form" method="get">
                        <input type="hidden" name="job_id" value="<?php echo $jobID; ?>">
                        <input type="hidden" name="activity_id" value="<?php echo $activityID; ?>">
                        <input type="text" class='form-control' name="comment" value="" autocomplete="off" autofocus>
                        <br>
                        <input type="submit" class='btn' value="Done">
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php getTemplatePart('footer') ?>