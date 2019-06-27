<?php include 'include/crm_init.php'; ?>

<div class="row panel panel-default actsbtns">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <h1>Choose Activity</h1>
    </div>
    <?php

    $activities = get_rows("activities");
    print_r($activities);

    $jobID = ph_validate_number($_GET['jid']);

    foreach ($activities as $activity) {
        $activityCategories[$activity['name']][$activity['type']] = $activity;
    }
    
    foreach ($activityCategories as $categoryName => $activities) {
        ?><h2><?php //echo $categoryName; ?></h2><?php
        foreach ($activities as $activity) {
            if ($jobID != 0) {
                if (($activity['ID'] > 0) and ($activity['ID'] < 11))
                    $href = 'nextshift';
            } else {
                if ($activity['ID'] >= 11)
                    $href = 'nextshift';
                if ($activity['name'] == 'other')
                    $href = 'othercomment';
            }
            if (!empty($href)) :
                $href = sprintf('%s.php?job_id=%s&activity_id=%s&furniture_id=%s', $href, $jobID, $activity['ID'], $jobID);
                ?>
                <div class="col-md-3 col-sm-4 col-xs-6">
                    <div class="btn main-btn">
                        <a href="<?php echo $href; ?>">
                            <img src="<?php echo $activity['image']; ?>"/>
                            <h3><?php echo $activity['name']; ?></h3>
                        </a>
                    </div>
                </div>
            <?php endif;
        }
    }
    
    
    foreach ($activities as $activity) {
        if ($jobID != 0) {
            if (($activity['ID'] > 0) and ($activity['ID'] < 11))
                $href = 'nextshift';
        } else {
            if ($activity['ID'] >= 11)
                $href = 'nextshift';
            if ($activity['name'] == 'other')
                $href = 'othercomment';
        }
        if (!empty($href)) :
            $href = sprintf('%s.php?job_id=%s&activity_id=%s&furniture_id=%s', $href, $jobID, $activity['ID'], $jobID);
            ?>
            <div class="col-md-3 col-sm-4 col-xs-6">
                <div class="btn main-btn">
                    <a href="<?php echo $href; ?>">
                        <img src="<?php echo $activity['image']; ?>"/>
                        <h2><?php echo $activity['name']; ?></h2>
                    </a>
                </div>
            </div>
        <?php endif;
    } ?>
</div>
<?php include 'include/footer.php' ?>
