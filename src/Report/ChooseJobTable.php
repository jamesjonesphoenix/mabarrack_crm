<?php


namespace Phoenix\Report;


use Phoenix\Entity\Job;

/**
 * Class ChooseJobTable
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
class ChooseJobTable extends Report
{
    /**
     *
     */
    protected string $title = 'Choose Job';

    /**
     * @var Job[]
     */
    protected array  $jobs;

    /**
     * @param Job[] $jobs
     * @return $this
     */
    public function init(array $jobs = []): self
    {
        $this->jobs = $jobs;
        return $this;
    }

    /**
     * @param Job $job
     * @return string
     */
    public function getSelectLinkURL(Job $job): string
    {
        $jobID = $job->id;
        $href = 'worker.php?job=' . $jobID;
        if ( $jobID === 0 ) {
            return $href . '&choose=activity';
        }

        $furniture = $job->furniture;
        if ( empty( $furniture ) ) {
            return '';
        }
        $numberOfFurniture = count( $furniture );
        if ( $numberOfFurniture === 1 ) {
            return $href . '&furniture=' . array_shift( $furniture )->id . '&choose=activity'; //no need to choose furniture because job only has one choice
        }
        return $href . '&choose=furniture';
    }

    /**
     * @return array
     */
    public function extractData(): array
    {
        foreach ( $this->jobs as $job ) {
            $shift = $job->shifts->getOne();
            $url = $this->getSelectLinkURL( $job );
            $healthCheck = $job->healthCheck();
            $jobTableData[$job->id] = [
                'id' => $job->id,
                'customer' => $job->customer->name ?? 'N/A',
                'description' => $job->description
            ];
            if ( empty( $healthCheck ) ) {
                $jobTableData[$job->id] = array_merge( $jobTableData[$job->id], [
                    'furniture-select-worked.furniture' => $job->getFurnitureString(false),
                    'furniture-select-worked.select' => '<p class="text-right mb-0">' . $this->htmlUtility::getButton( [
                            'element' => 'a',
                            'href' => $url,
                            'class' => 'btn btn-primary btn-lg',
                            'content' => 'Select',
                            'disabled' => true
                        ] ) . '</p>',
                    'furniture-select-worked.last_worked' =>  $shift->date ?? ''
                ] );
            } else {
                $jobTableData[$job->id]['furniture-select-worked'] = $this->htmlUtility::getAlertHTML(
                    '<p class="">Job ' . $job->id . ' cannot be selected:</p>' . $healthCheck,
                    'danger', false );
            }
        }
        return $jobTableData ?? [];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function renderReport(): string
    {
        $jobTableData = $this->extractData();
        if ( empty( $jobTableData ) ) {
            return $this->htmlUtility::getAlertHTML( 'No jobs to choose from.', 'info', false );
        }

        foreach ( $jobTableData as &$tableRow ) {
            if ( !empty( $tableRow['last_worked'] ) ) {
                $tableRow['last_worked'] = $this->format::date( $tableRow['last_worked'] );
                $tableRow['last_worked'] = '<span class="text-nowrap">' . $this->format::daysFromTodayToWords( $tableRow['last_worked'], true ) . '</span>';
            } else {
                $tableRow['last_worked'] = '-';
            }
        }

        return $this->htmlUtility::getTableHTML( [
            'data' => $jobTableData,
            'columns' => [
                'id' => 'ID',
                'customer' => 'Customer',
                'description' => 'Description',
                'furniture-select-worked.furniture' => 'Furniture',
                'furniture-select-worked.select' => '',
                'furniture-select-worked.last_worked' => 'Last Worked By You',
            ],
            'class' => 'choose-job'
        ] );
    }
}