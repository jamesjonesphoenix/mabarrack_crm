<?php


namespace Phoenix\Report;


use Phoenix\Entity\Activity;

/**
 * Class ChooseActivityTable
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class ChooseActivityTable extends Report
{
    /**
     *
     */
    protected string $title = 'Choose Activity';

    /**
     * @var Activity[]
     */
    private array $activities;

    /**
     * @var array
     */
    private array $activityURLs;

    /**
     * @param Activity[][][] $activities
     * @param array          $activityURLs
     * @return $this
     */
    public function init(array $activities = [], array $activityURLs = []): self
    {
        $this->activities = $activities;
        $this->activityURLs = $activityURLs;

        //$this->title = '<small>Job ID:</small> ' . $job->id  . '';
        return $this;
    }

    /**
     * @return array
     */
    public function getSortedActivities(): array
    {
        $activityCategories = [];
        $unsortedActivities = [];
        foreach ( $this->activities as $activity ) {
            $unsortedActivities[$activity->category][$activity->type][$activity->name] = $activity;
            if ( !in_array( $activity->category, $activityCategories, true ) ) {
                $activityCategories[$activity->id] = $activity->category;
            }
        }
        $sortedActivities = [];
        foreach ( $activityCategories as $activityCategory ) {
            $sortedActivities[$activityCategory] = $unsortedActivities[$activityCategory];
        }
        return $sortedActivities ?? [];
    }

    /**
     * @return array
     */
    public function getActivityTypes(): array
    {
        foreach ( $this->activities as $activity ) {
            //if ( !in_array( $activity->type, $activityTypes, true ) ) {
            $activityTypes[$activity->type] = $activity->type;
            //}

        }
        return $activityTypes ?? [];
    }

    /**
     * @param Activity $activity
     * @param string   $url
     * @return string
     */
    public
    function getActivityIcon(Activity $activity, string $url = ''): string
    {
        ob_start(); ?>
        <a href="<?php echo $url; ?>">
            <div class="activityIconImageContainer"><img
                        src="img/activities/<?php echo $activity->image; ?>"
                        alt="<?php echo $activity->name; ?>"/></div>
            <span><?php echo $activity->displayName; ?></span>
        </a>
        <?php
        return ob_get_clean();
    }

    /**
     * @return array
     */
    public
    function extractData(): array
    {
        $sortedActivities = $this->getSortedActivities();

        $activityURLs = $this->activityURLs;
        foreach ( $sortedActivities as $categoryName => $activityTypes ) {
            $activityTableData[$categoryName]['activity-category'] = $categoryName;
            foreach ( $this->getActivityTypes() as $type ) {
                $cell = '';
                if ( !empty( $activityTypes[$type] ) ) {
                    $i = 0;
                    //$cell .= '<div class="text-nowrap">';
                    $cell .= '<div>';
                    foreach ( $activityTypes[$type] as $activity ) {
                        if ( $i > 1 && ($i % 2) === 0 ) { //even
                            //$cell .= '</div><div class="text-nowrap">';
                            $cell .= '</div><div>';
                        }
                        $cell .= $this->getActivityIcon( $activity, $activityURLs[$activity->id] );
                        $i++;
                    }
                    $cell .= '</div>';
                }
                $activityTableData[$categoryName]['activity-category-' . $type] = $cell;
            }
        }
        return $activityTableData ?? [];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public
    function renderReport(): string
    {
        $activityTableData = $this->extractData();
        if ( empty( $activityTableData ) ) {
            return $this->htmlUtility::getAlertHTML( 'Job ' . /*$this->job->id*/ 45 . ' has no furniture to choose from.', 'info' );
        }
        $activityTypes = $this->getActivityTypes();
        $columns = ['activity-category' => ''];
        foreach ( $activityTypes as $activityType ) {
            $columns['activity-category-' . $activityType] = $activityType;
        }

        return $this->htmlUtility::getTableHTML( [
            'data' => $activityTableData,
            'columns' => $columns,
            'class' => ['choose-activity'],
            'subheaders' => [
                'columns' => ['activity-category']
            ],
            'columnsClasses' => [
                'activity-category' => 'text-right'
            ]
        ] );
    }
}