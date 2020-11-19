<?php


namespace Phoenix\Page;


/**
 * Class AdminPageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
abstract class AdminPageBuilder extends PageBuilder
{
    /**
     * @var string
     */
    protected string $groupBy = '';

    /**
     * @var string
     */
    protected string $sortActivitiesBy = '';

    /**
     * @var bool Flag whether we display ActivitySummary in separate tables or not
     */
    protected bool $groupActivities = false;

    /**
     * @param array $inputArgs
     * @return $this
     */
    public function setInputArgs(array $inputArgs = []): self
    {
        if ( !empty( $inputArgs['sort_activities_by'] ) ) {
            $this->sortActivitiesBy = $inputArgs['sort_activities_by'];
        }
        if ( !empty( $inputArgs['group_activities'] ) ) {
            $this->groupActivities = true;
        }
        if ( !empty( $inputArgs['group_by'] ) ) {
            $this->groupBy = $inputArgs['group_by'];
        }
        return $this;
    }


}