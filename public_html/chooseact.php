<?php include 'include/crm_init.php'; ?>

<?php
$jobID = ph_validate_number($_GET['job_id']);
$activityCategories = [];
$activityTypes = [];
foreach (get_rows("activities") as $activity) {
    if ($activity['name'] == 'Lunch')
        continue;
    if ($jobID == 0 && $activity['factoryOnly'] != 1)
        continue;
    if ($jobID > 0 && $activity['factoryOnly'] == 1)
        continue;
    $href = $activity['name'] == 'other' ? 'othercomment' : 'nextshift';

    $activity['href'] = sprintf('%s.php?job_id=%s&activity_id=%s&furniture_id=%s', $href, $jobID, $activity['ID'], $jobID);
    $activityType = !empty($activity['type']) ? $activity['type'] : 'Manual';
    if (!in_array($activityType, $activityTypes))
        $activityTypes[] = $activityType;
    $activityCategories[$activity['name']][$activityType] = $activity;
}
?>

<div class="row panel panel-default actsbtns">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <h1>Choose Activity</h1>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th scope="col">Activity Type</th>
            <th colspan="2" scope="col">Choices</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($activityCategories as $categoryName => $activities) : ?>
            <tr>
                <th scope="row"><?php echo $categoryName; ?></th>
                <?php foreach ($activityTypes as $type) : ?>
                    <td>
                        <?php if (!empty($activities[$type])) : ?>
                            <a href="<?php echo $activities[$type]['href']; ?>">
                                <img src="<?php echo $activities[$type]['image']; ?>"/>
                                <p><?php echo $activities[$type]['type']; ?></p>
                            </a>

                        <?php else : ?>
                            &nbsp;
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include 'include/footer.php' ?>
