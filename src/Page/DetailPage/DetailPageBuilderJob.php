<?php

namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\CustomerFactory;
use Phoenix\Entity\FurnitureFactory;
use Phoenix\Entity\Job;
use Phoenix\Entity\JobFactory;
use Phoenix\Entity\SettingFactory;
use Phoenix\Entity\ShiftFactory;
use Phoenix\Form\DetailPageForm\JobEntityForm;
use Phoenix\Page\MenuItems\MenuItemsJobs;
use Phoenix\Report\Archive\ArchiveTableJobShifts;
use Phoenix\Report\JobSummary;
use Phoenix\Report\Shifts\ActivitySummary;

/**
 * @method Job getEntity(int $entityID = null)
 *
 * Class DetailPageBuilderJob
 *
 * @author James Jones
 * @package Phoenix\DetailPage
 *
 */
class DetailPageBuilderJob extends DetailPageBuilder
{
    /**
     * @return JobFactory
     */
    protected function getNewEntityFactory(): JobFactory
    {
        return new JobFactory( $this->db, $this->messages );
    }

    /**
     * @return JobEntityForm
     */
    public function getForm(): JobEntityForm
    {
        $entity = $this->getEntity();
        $jobStatuses = $this->db->getRows( 'settings', ['name' => [
            'value' => 'jobstat',
            'operator' => 'LIKE']
        ] );

        //$settingsFactory = (new SettingFactory($this->db, $this->messages))->getOptionsArray();

        return (new JobEntityForm(
            $this->HTMLUtility,
            $entity
        ))->makeOptionsDropdownFields(
            array_column( $jobStatuses, 'value', 'name' ),
            (new CustomerFactory( $this->db, $this->messages ))->getOptionsArray(),
            (new FurnitureFactory( $this->db, $this->messages ))->getOptionsArray()
        );
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
     * @return $this
     */
    public function addReports(): self
    {
        $entity = $this->getEntity();
        if ( !$entity->exists ) {
            return $this;
        }
        $format = $this->format;
        $htmlUtility = $this->HTMLUtility;
        $reports = [
            'job_summary' => (new JobSummary(
                $htmlUtility,
                $format,
            ))->init( $entity ),
            'activity_summary' => (new ActivitySummary(
                $htmlUtility,
                $format,
            ))->init( $entity->shifts ),
            'job_shifts' => (new ArchiveTableJobShifts(
                $htmlUtility,
                $format,
            ))->setEntities(
                $entity->shifts->getAll(),
                (new ShiftFactory( $this->db, $this->messages ))->getNew()
            )->setGroupByForm(
                $this->getGroupByForm(),
                $this->groupBy
            )->setTitle( 'Job <span class="badge badge-primary">ID: ' . $entity->id . '</span> Shifts' )
        ];
        foreach ( $reports as $report ) {
            $this->page->addContent( $report->render() );
        }
        return $this;
    }
}