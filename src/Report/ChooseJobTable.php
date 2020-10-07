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
     * @var string
     */
    protected string $emptyMessage = 'No jobs to choose from.';

    /**
     * @var string
     */
    protected string $emptyMessageClass = 'info';

    /**
     * @var array
     */
    protected array $columns = [
        'id' => 'ID',
        'left.description' => [
            'title' => 'Description',
            'default' => '&minus;'
        ],
        'left.customer' => [
            'title' => 'Customer',
            'default' => '&minus;'
        ],
        'left.furniture' => [
            'title' => 'Furniture',
            'default' => '&minus;'
        ],
        'right.select' => [
            'title' => '',
            'default' => '&minus;'
        ],
        'right.last_worked' => [
            'title' => 'Last Worked By You',
            'default' => '&minus;'
        ],
    ];

    /**
     * @var string
     */
    protected string $tableClass = 'choose-job';

    /**
     * @param Job[] $jobs
     * @return $this
     */
    public function setJobs(array $jobs = []): self
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
        $href = 'worker.php?job=' . $job->id;
        if ( $job->id === 0 ) {
            return $href . '&choose=activity';
        }

        $furniture = $job->furniture;
        if ( empty( $furniture ) ) {
            return '';
        }
        if ( count( $furniture ) === 1 ) {
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
                'id' => 'Factory',
                'left.description' => 'Non-billable internal factory work.',
            ], $rightHandCells );
        }

        $healthCheck = $job->healthCheck();
        $data = [
            'id' => $job->id,
            'left.customer' => $job->customer->name,
            'left.description' => $job->description,
            'left.furniture' => $job->getFurnitureString( false ),
        ];
        if ( empty( $healthCheck ) ) {
            return array_merge( $data, $rightHandCells );
        }
        $data['right'] = $this->htmlUtility::getAlertHTML(
            '<p class="">Job ' . $job->id . ' cannot be selected:</p>' . $this->htmlUtility::getListGroup( $healthCheck ),
            'danger', false );
        return $data;

    }

    /**
     * @return array
     */
    public function extractData(): array
    {
        foreach ( $this->jobs as $job ) {
            $data[$job->id] = $this->extractJobData( $job );
        }
        if ( !empty( $data ) && count( $data ) === 1 && key( $data ) === 0 ) {
            $this->columns['left.customer']['title'] = '';
            $this->columns['left.furniture']['title'] = '';
        }
        return $data ?? [];
    }
}