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
     * @var bool
     */
    protected bool $fullwidth = false;

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
            $i = 0;
            $iconHTML = '';
            foreach ( $activities as $activityName => $activity ) {
                $i++;
                $iconHTML .= $this->getActivityIcon( $activity, $activityURLs[$activity->id] );
            }
            if ( $i > 1 ) {
                $activityIconsMulti[$categoryName] = $iconHTML;
            } else {
                $activityIconsSingle[$categoryName] = $iconHTML;
            }
        }
        return [
            'multiple' => $activityIconsMulti ?? [],
            'single' => $activityIconsSingle ?? []
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public
    function renderReport(): string
    {
        $activityIcons = $this->extractData();
        ob_start(); ?>
        <div class="m-n2 clearfix">
            <div class="clearfix"><?php echo implode( '', $activityIcons['single'] ); ?></div>
            <div class="my-2"><?php
                foreach ( $activityIcons['multiple'] as $categoryName => $iconHTML ) {
                    ?>
                    <div class="row align-items-center choose-activity mx-2 my-2 py-2">
                        <div class="col-auto px-0">
                            <span><?php echo $this->type . ' ' . $categoryName; ?></span>
                        </div>
                        <div class="col px-0"><?php echo $iconHTML; ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }
}