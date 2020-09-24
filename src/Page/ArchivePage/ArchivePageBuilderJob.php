<?php


namespace Phoenix\Page\ArchivePage;


use Phoenix\Entity\JobFactory;
use Phoenix\Entity\SettingFactory;
use Phoenix\Page\MenuItems\MenuItemsJobs;
use Phoenix\Report\Archive\ArchiveTable;
use Phoenix\Report\Archive\ArchiveTableJobs;

/**
 * Class ArchivePageBuilderJob
 *
 * @author James Jones
 * @package Phoenix\ArchivePage
 *
 */
class ArchivePageBuilderJob extends ArchivePageBuilder
{
    /**
     * @var array
     */
    protected array $queryArgs = ['id' => ['operator' => '!=', 'value' => 0]];

    /**
     * @var array
     */
    protected array $provisionArgs = [
        'furniture' => true,
        'shifts' => [
            'activity' => false,
            'furniture' => false,
            'worker' => ['shifts' => false],
            'job' => false
        ],
        'customer' => ['jobs' => false]
    ];

    /**
     * @var array
     */
    protected array $inessentialColumns = [];

    /**
     * @return JobFactory
     */
    protected function getNewEntityFactory(): JobFactory
    {
        return new JobFactory( $this->db, $this->messages );
    }

    /**
     * @return MenuItemsJobs
     */
    public function getMenuItems(): MenuItemsJobs
    {
        return (new MenuItemsJobs( $this->getEntityFactory() ))
            ->setJobUrgencyThreshold(
                new SettingFactory( $this->db, $this->messages )
            );
    }

    /**
     * @return string
     */
    protected function getTitlePrefix(): string
    {
        $statusName = $this->inputArgs['query']['status'] ?? '';
        $priority = $this->inputArgs['query']['priority'] ?? '';

        $settingFactory = new SettingFactory( $this->db, $this->messages );
        if ( !empty( $priority ) ) {
            $priorityUrgencyThreshold = $settingFactory->getSetting( 'joburg_th' );
            if ( (int)$priority <= $priorityUrgencyThreshold && $statusName === 'jobstat_red' ) {
                return $this->HTMLUtility::getIconHTML( 'exclamation-triangle' ) . ' Urgent';
            }
        } elseif ( !empty( $statusName ) ) {
            return $this->entityFactory->getNew()->getIcon() . ' ' . $settingFactory->getSetting( $statusName );
        }
        return parent::getTitlePrefix();
    }

    /**
     * @return ArchiveTableJobs
     */
    protected function getNewArchiveTableReport(): ArchiveTableJobs
    {
        return new ArchiveTableJobs( $this->HTMLUtility, $this->format );
    }
}