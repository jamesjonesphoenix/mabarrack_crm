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
        return (new JobEntityForm(
            $this->HTMLUtility,
            $this->getEntity()
        ))->makeOptionsDropdownFields(
            (new SettingFactory( $this->db, $this->messages ))->getJobStatusesOptionsArray(),
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

        $reportFactory = $this->getReportClient()->getFactory();

        $jobSummary = $reportFactory->getJobSummary()
            ->setJob( $entity );

        if ( $entity->id === 0 ) {
            $jobSummary->setEmptyMessage(
                'This is the internal factory job for holding non-billable job activities like cleaning, lunch etc. It\'s shifts can be viewed, but the job itself cannot be edited.'
            )
                ->setEmptyMessageClass(
                    'primary'
                );
        }

        $reports = [
            'job_summary' => $jobSummary,
            'activity_summary' => $reportFactory->shiftsReports()->getActivitySummary( $this->sortActivitiesBy, $this->groupActivities )
                ->setEntities( $entity->shifts )
                ->setTitle( 'Activities Summary for Job ' . $entity->getIDBadge() )
                ->removeSortableOption( 'factory' )
            ,
            'job_shifts' => $reportFactory->archiveTables()->getShifts()
                ->setEntities(
                    $entity->shifts,
                )
                ->setDummyEntity( (new ShiftFactory( $this->db, $this->messages ))->getNew() )
                ->setGroupByForm(
                    $this->getGroupByForm(),
                    $this->groupBy
                )
                ->setTitle( 'Job ' . $entity->getIDBadge() . ' Shifts' )
                ->editColumn( 'job', ['hidden' => true] )
                ->disablePrintButton()
                ->setEmptyMessage( 'No job activity to report.' )
        ];

        //if($this->groupBy)

        //$reports['activity_summary']->setTitle( $reports['activity_summary']->getTitle() . ' for Job ' . $entity->getIDBadge() );
        foreach ( $reports as $report ) {
            $this->page->addContent( $report->render() );
        }
        return $this;
    }
}