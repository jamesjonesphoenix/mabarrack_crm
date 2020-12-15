<?php

namespace Phoenix\Page\WorkerChoose;

use Phoenix\Entity\JobFactory;

/**
 * Class ChoosePageBuilderFurniture
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class ChoosePageBuilderFurniture extends ChoosePageBuilder
{
    /**
     * @var int|null
     */
    private ?int $jobID;

    /**
     * @var string
     */
    protected string $title = 'Choose Furniture';

    /**
     * @param int|null $jobID
     * @return $this
     */
    public function setJobID(int $jobID = null): self
    {
        $this->jobID = $jobID;
        return $this;
    }

    /**
     * @return $this
     */
    public function addTitle(): self
    {
        $this->page->setTitle(
            $this->HTMLUtility::getIconHTML( 'chair' )
            . ' Choose Furniture for Job'
            . $this->HTMLUtility::getBadgeHTML( $this->jobID )
        );
        return $this;
    }

    /**
     * @return string[][]
     */
    public function getMenuItems(): array
    {
        return array_merge( [
            'choose_job' => [
                'href' => 'employee.php?choose=job',
                'content' => 'Choose Different Job',
                'class' => 'bg-info'
            ]],
            parent::getMenuItems()
        );
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function addChooseTables(): self
    {
        $jobID = $this->jobID ?? null;
        if ( $jobID === null ) {
            return $this;
        }
        $job = (new JobFactory( $this->db, $this->messages ))->getJob( $jobID );

        $this->page->addContent(
            $this->getReportClient()->getFactory()->getChooseFurnitureTable()
                ->setJobs( $job )
                ->render() );

        return $this;
    }
}