<?php


namespace Phoenix\Report\Shifts;

use Phoenix\Entity\Shifts;
use Phoenix\Entity\User;
use Phoenix\Report\Archive\ArchiveTableShifts;
use Phoenix\Report\Report;
use Phoenix\Report\ReportBuilder;
use Phoenix\Report\Shifts\Worker\WorkerWeekReport;
use Phoenix\Report\Shifts\Worker\WorkerWeekSummary;
use Phoenix\Report\Shifts\Worker\WorkerWeekTimeClockRecord;

/**
 * @author James Jones
 * @property Shifts|null $entities
 *
 * Class ShiftsReportBuilder
 *
 * @package Phoenix\Report\Shifts
 *
 */
class ShiftsReportBuilder extends ReportBuilder
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @var bool
     */
    private bool $userRequired = false;

    /**
     * @return ShiftsReportFactory
     */
    protected function getFactory(): ShiftsReportFactory
    {
        return parent::getFactory()->shiftsReports();
    }

    /**
     * @return ArchiveTableShifts
     */
    public function getWeekShiftsArchive(): ArchiveTableShifts
    {
        $this->report = parent::getFactory()->archiveTables()->getShifts()
            ->setTitle( 'Week Shifts Archive' )
            /*
            ->setGroupByForm(
                $this->getGroupByForm()
                    ->makeHiddenFields( ['date_start' => $dateStart] ),
                $this->groupBy
            )
            */
            ->editColumn( 'worker', ['hidden' => true] )->setEmptyMessage();
        $this->provisionReport();
        return $this->report;
    }

    /**
     * @return WorkerWeekTimeClockRecord
     */
    public function getTimeClockRecord(): WorkerWeekTimeClockRecord
    {
        $this->setUserRequired();
        $this->report = $this->getFactory()->getTimeClockRecord()
            ->setDateStart( $this->dateStart );
        $this->provisionReport();
        return $this->report;
    }

    /**
     * @return WorkerWeekSummary
     */
    public function getWorkerWeekSummary(): WorkerWeekSummary
    {
        $this->setUserRequired();
        $this->report = $this->getFactory()->getWorkerWeekSummary()
            ->setDateStart( $this->dateStart );
        $this->provisionReport();
        return $this->report;
    }

    /**
     * @param string $sortBy
     * @param bool   $groupSeparateTables
     * @return ActivitySummary
     */
    public function getActivitySummary(string $sortBy = '', bool $groupSeparateTables = false): ActivitySummary
    {
        $this->report = $this->getFactory()->getActivitySummary( $sortBy, $groupSeparateTables );
        $this->provisionReport();
        return $this->report;
    }


    /**
     * @param User|null $user
     * @return $this
     */
    public function setUser(User $user = null): self
    {
        if ( $user !== null ) {
            $this->user = $user;
        }
        $this->resetEntities();
        return $this;
    }

    /**
     * @return $this
     */
    public function setUserRequired(): self
    {
        $this->userRequired = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function userRequiredAndMissing(): bool
    {
        return $this->userRequired && !isset( $this->user );
    }


    /**
     * @return Shifts
     */
    public function getEntities(): Shifts
    {
        if ( $this->userRequiredAndMissing() ) {
            return new Shifts();
        }
        if ( $this->entities !== null ) {
            return $this->entities;
        }
        $queryArgs = [
            'date' => [
                'value' => [
                    'start' => $this->dateStart,
                    'finish' => $this->dateFinish
                ],
                'operator' => 'BETWEEN'
            ],
            /*  'time_finished' => [
                  'value' => '',
                  'operator' => '!='
              ] */
        ];
        if ( isset( $this->user ) ) {
            $queryArgs['worker'] = $this->user->id;
        }
        return $this->entities = new Shifts(
            $this->entityFactory->getEntities( $queryArgs, [
                'activity' => true,
                'worker' => [
                    'shifts' => false
                ],
                'job' => [
                    'customer' => true
                ]
            ] )
        );
    }


    /**
     * @param string $dateStart
     * @return $this
     */
    public function setDatesForWeek(string $dateStart = ''): self
    {
        $this->resetEntities();
        $dateFormat = 'Y-m-d';
        if ( !empty( $dateStart ) && strtotime( $dateStart ) !== strtotime( date( 'Y-m-d' ) ) ) { /*Date provided and not today*/
            $this->dateStart = $dateStart;
            $this->dateFinish = date( $dateFormat, strtotime( $dateStart . ' + 6 days' ) );
            return $this;
            /*
            return [
                'date_start' => $dateStart,
                'date_finish' => date( $dateFormat, strtotime( $dateStart . ' + 6 days' ) )
            ];
            */
        }
        $weekDay = date( 'w' );
        $dateStartTimestamp = $weekDay === '5' /*Friday*/ ? time() : strtotime( 'previous friday' );

        $dateFinishTimestamp = $weekDay === '4' /*Thursday*/ ? time() : strtotime( 'next thursday' );
        $this->dateStart = date( $dateFormat, $dateStartTimestamp );
        $this->dateFinish = date( $dateFormat, $dateFinishTimestamp );
        return $this;
        /*
        return [
            'date_start' => date( $dateFormat, $dateStartTimestamp ),
            'date_finish' => date( $dateFormat, $dateFinishTimestamp )
        ];
        */

    }

    /**
     * @param string $title
     * @return string
     */
    public function annotateTitleWithInputs(string $title = ''): string
    {
        $username = isset( $this->user ) ? '<small>' . $this->htmlUtility::getBadgeHTML( $this->user->getFirstName() ) . '</small> ' : '';
        return parent::annotateTitleWithInputs( $username . $title );
    }

    /**
     * @return string
     */
    public function validateInputs(): string
    {
        if ( $this->userRequiredAndMissing() ) {
            return 'Please set a worker.';
        }
        return parent::validateInputs();
    }

    /**
     * @return string
     */
    public function getDefaultEmptyMessage(): string
    {
        return 'No completed shifts '
            . (isset( $this->user ) ? 'for worker ' . $this->htmlUtility::getBadgeHTML( $this->user->getFirstName(), 'primary' ) : '')
            . ' found between'
            . $this->getDateString( 'primary' )
            . ' to report.';
    }


}