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
     * @var Activity[][]
     */
    private array $activities = [];

    /**
     * @var array
     */
    private array $activityURLs;

    private string $type = '';

    /**
     * @param Activity[] $activities
     * @param array      $activityURLs
     * @param string     $type
     * @return $this
     */
    public function init(array $activities = [], array $activityURLs = [], string $type = ''): self
    {
        $this->activityURLs = $activityURLs;
        $this->type = $type;
        $this->setTitle( $type . ' Activities' );


        foreach ( $activities as $activity ) {
            $this->activities[$activity->category][$activity->name] = $activity;
        }
        return $this;
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
        <a class="activity-icon-link m-2" href="<?php echo $url; ?>">
            <div class="activity-icon-image-container"><img
                        src="img/activities/<?php echo $activity->image; ?>"
                        alt="<?php echo $activity->name; ?>"/></div>
            <span><?php echo $activity->displayName; ?></span>
        </a>
        <?php return ob_get_clean();
    }

    /**
     * @return array
     */
    public
    function extractData(): array
    {
        $activityURLs = $this->activityURLs;
        foreach ( $this->activities as $categoryName => $activities ) {
            if ( count( $activities ) === 1 ) {
                $activity = current( $activities );
                $returnData[$categoryName] = $this->getActivityIcon( $activity, $activityURLs[$activity->id] );
                continue;
            }
            foreach ( $activities as $activityName => $activity ) {
                $returnData[$categoryName][$activity->name] = $this->getActivityIcon( $activity, $activityURLs[$activity->id] );
            }
        }
        return $returnData ?? [];

    }

    /**
     * @param array  $activities
     * @param string $categoryName
     * @return string
     * @throws \Exception
     */
    public
    function makeTable(array $activities, string $categoryName = ''): string
    {
        d( $activities );
        return '<div class="row align-items-center choose-activity mx-2 my-2 py-2"><div class="col-auto px-0"><span>'. $categoryName . '</span></div><div class="col px-0">'. implode('', $activities) . '</div>';
        /*
        $columns = [
            'header' => ['subheader' => true],
            'activity' => ''
        ];
        $data[0] = ['header' => '<span>'. $categoryName . '</span>' , 'activity' => implode('', $activities)];
        return $this->htmlUtility::getTableHTML( [
            'data' => $data ?? [],
            'class' => ['choose-activity m-2'],
            'columns' => $columns
        ] );
        */
    }

    /**
     * @return string
     * @throws \Exception
     */
    public
    function renderReport(): string
    {

        $activityChooseButtons = $this->extractData();
        if ( empty( $activityChooseButtons ) ) {
            return $this->htmlUtility::getAlertHTML( 'No activities available to choose from.', 'danger' );
        }
        $html = '<div class="clearfix">';
        foreach ( $activityChooseButtons as $categoryName => $activities ) {
            if ( is_string( $activities ) ) {
                $html .= $activities;
            }
        }
        $html .= '</div>';
        foreach ( $activityChooseButtons as $categoryName => $activities ) {
            if ( is_array( $activities ) ) {
                $html .= $this->makeTable( $activities, $this->type . ' ' . $categoryName );
            }

        }
        return '<div class="m-n2 clearfix">' . $html . '</div>';
    }
}