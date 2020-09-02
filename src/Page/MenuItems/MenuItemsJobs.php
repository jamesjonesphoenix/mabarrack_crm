<?php

namespace Phoenix\Page\MenuItems;


use Phoenix\Entity\Entities;
use Phoenix\Entity\SettingFactory;

/**
 * Class MenuItemsJobs
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class MenuItemsJobs extends MenuItemsEntities
{
    /**
     * @var int
     */
    private int $jobUrgencyThreshold = 1000000;

    /**
     * @var int
     */
    protected int $maxErrorsToCheck = 50;

    /**
     * @var array|bool
     */
    protected $provisionArgsForHealthCheck = ['furniture' => true];

    /**
     * @param SettingFactory $settingFactory
     * @return $this
     */
    public function setJobUrgencyThreshold(SettingFactory $settingFactory): self
    {
        $this->jobUrgencyThreshold = (integer)($settingFactory->getSetting( 'joburg_th' )) + 1;
        return $this;
    }

    /**
     * @return array[]
     */
    public function getEntityMenuItems(): array
    {
        $entity = $this->entityFactory->getNew();
        return [
            'In Progress' => [
                'text' => 'In Progress',
                'url' => $entity->getArchiveLink() . '&query[status]=jobstat_red',
                'number' => $this->entityFactory->getCount( [
                    'status' => 'jobstat_red',
                    'ID' => ['operator' => '!=', 'value' => 0]
                ] ),
                'icon' => 'hammer'
            ],
            'Urgent' => [
                'text' => 'Urgent Jobs',
                'url' => $entity->getArchiveLink() . '&query[status]=jobstat_red&query[priority]=1',
                'number' => $this->entityFactory->getCount( [
                    'status' => 'jobstat_red',
                    'ID' => ['operator' => '!=', 'value' => 0],
                    'priority' => [
                        'operator' => '<',
                        'value' => $this->jobUrgencyThreshold
                    ]
                ] ),
                'icon' => 'exclamation-triangle'
            ]
        ];
    }

}