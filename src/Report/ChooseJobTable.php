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
     * @param Job $job
     * @return array
     */
    public function extractJobData(Job $job): array
    {
        $lastWorked = $job->shifts->getOne()->date ?? '';
        if ( !empty( $lastWorked ) ) {
            $lastWorked = '<span class="text-nowrap">'
                . $this->format::daysFromTodayToWords( $this->format::date( $lastWorked ), true )
                . '</span>';
        } else {
            $lastWorked = '-';
        }

        $rightHandCells = [
            'right.select' => '<p class="text-right mb-0">' . $this->htmlUtility::getButton( [
                    'element' => 'a',
                    'href' => $this->getSelectLinkURL( $job ),
                    'class' => 'btn btn-primary btn-lg',
                    'content' => 'Select',
                    'disabled' => true
                ] ) . '</p>',
            'right.last_worked' => $lastWorked
        ];
        if ( $job->id === 0 ) {
            return array_merge( [
                'left' => 'Non-billable internal factory work.',
            ], $rightHandCells );
        }
        $healthCheck = $job->healthCheck();
        $data = [
            'left.id' => $job->id,
            'left.customer' => $job->customer->name ?? 'N/A',
            'left.description' => $job->description,
            'left.furniture' => $job->getFurnitureString( false ),
        ];



        if ( empty( $healthCheck ) ) {
            return array_merge( $data, $rightHandCells );
        }
        $data['right'] = $this->htmlUtility::getAlertHTML(
            '<p class="">Job ' . $job->id . ' cannot be selected:</p>' . $healthCheck,
            'danger', false );
        return $data;

    }

    /**
     * @return array
     */
    public function extractData(): array
    {
        foreach ( $this->jobs as $job ) {
            $jobTableData[$job->id] = $this->extractJobData( $job );
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
        $columnTitles = [
            'left.id' => 'ID',
            'left.customer' => 'Customer',
            'left.description' => 'Description',
            'left.furniture' => 'Furniture',
            'right.select' => '',
            'right.last_worked' => 'Last Worked By You',
        ];
        if ( count( $jobTableData ) === 1 && key( $jobTableData ) === 0 ) {
            $columnTitles = [
                'left.description' => 'Description',
                'left.id' => '',
                'left.customer' => '',
                'left.furniture' => '',
                'right.select' => '',
                'right.last_worked' => 'Last Worked By You',
            ];
        }
        return $this->htmlUtility::getTableHTML( [
            'data' => $jobTableData,
            'columns' => $columnTitles ?? [],
            'class' => 'choose-job'
        ] );
    }
}