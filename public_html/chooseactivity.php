<?php

namespace Phoenix;

include '../src/crm_init.php'; ?>

<?php

$jobID = !empty( $_GET['job_id'] ) ? ph_validate_number( $_GET['job_id'] ) : 0;
$furnitureID = !empty( $_GET['furniture_id'] ) ? ph_validate_number( $_GET['furniture_id'] ) : 0;


$unsortedActivities = [];
$allActivityTypes = [];

$activities = new Activities( PDOWrap::instance() );


foreach ( $activities->getActivities() as $activity ) {
    if ( $activity['name'] === 'Lunch' ) {
        continue;
    }
    if ( empty( $jobID ) && !$activities->factoryOnly( $activity['ID'] ) ) {
        continue;
    }
    if ( $jobID > 0 && $activities->factoryOnly( $activity['ID'] ) ) {
        continue;
    }
    if ( !$activities->isActive( $activity['ID'] ) ) {
        continue;
    }

    $href = strtolower( $activity['name'] ) === strtolower( 'other' ) ? 'othercomment' : 'nextshift';
    $activity['href'] = sprintf( '%s.php?job_id=%s&activity_id=%s&furniture_id=%s', $href, $jobID, $activity['ID'], $furnitureID );

    $activityType = $activity['type'] ?? 'Manual';
    if ( !in_array( $activityType, $allActivityTypes, true ) ) {
        $allActivityTypes[] = $activityType;
    }
    $unsortedActivities[$activity['category']][$activityType][$activity['name']] = $activity;
}

//terrible code
if ( $jobID > 0 ) {
    $activityCategories = array(
        'Planning',
        'Set Out',
        'Cutting',
        'Machining',
        'Assembly',
        'Polishing',
        'Fit Up',
        'Delivery',
        'Pick Up',
        'Rework'
    );

    foreach ( $activityCategories as $activityCategory ) {
        $sortedActivities[$activityCategory] = $unsortedActivities[$activityCategory];
    }
} else {
    $sortedActivities = $unsortedActivities;
}
?>

<div class="row panel panel-default actsbtns">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <h1>Choose Activity</h1>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th scope="col">Activity Category</th>
            <th scope="col"><?php echo $jobID > 0 ? 'Manual ' : ''; ?>Activities</th>


            <?php echo $jobID > 0 ? '<th scope="col">CNC Activities</th>' : ''; ?>

        </tr>
        </thead>
        <tbody>
        <?php foreach ( $sortedActivities as $categoryName => $activityTypes ) : ?>
            <tr>
                <th scope="row"><span><?php echo $categoryName; ?></span></th>
                <?php foreach ( $allActivityTypes as $type ) : ?>
                    <td class="activityIcon">
                        <?php if ( !empty( $activityTypes[$type] ) ) :
                            foreach ( $activityTypes[$type] as $activity ) : ?>
                                <a href="<?php echo $activity['href']; ?>">
                                    <div class="activityIconImageContainer"><img
                                                src="img/activities/<?php echo $activity['image']; ?>"
                                                alt="<?php echo $activity['name']; ?>"/></div>
                                    <span><?php echo $activities->getDisplayName( $activity['ID'] ) ?></span>
                                </a>
                            <?php
                            endforeach;
                        endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php ph_get_template_part( 'footer' ) ?>
