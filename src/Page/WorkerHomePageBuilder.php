<?php

namespace Phoenix\Page;

use Phoenix\Entity\CurrentUser;
use Phoenix\Entity\SettingFactory;
use Phoenix\Entity\User;
use Phoenix\Report\Worker\WorkerTimeClockRecord;
use Phoenix\Report\Shifts\WorkerHomeShiftTable;

/**
 * Class WorkerPageBuilder
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class WorkerHomePageBuilder extends PageBuilder
{
    /**
     * @var WorkerHomePage
     */
    protected WorkerHomePage $page;

    /**
     * @var string
     */
    private string $startDate = '';

    /**
     * @var User
     */
    protected User $user;

    /**
     * @param string $startDate
     * @return $this
     */
    public function addStartDate(string $startDate = ''): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @param CurrentUser $user
     * @return $this
     */
    public function addUser(CurrentUser $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage()->setTitle($this->user->getNamePossessive() . ' Dashboard');
        $this->addActionButtons();
        $this->addNews();
        $this->addWorkerHoursSummary();
        $this->addReports();
        return $this;

    }

    /**
     * @return WorkerHomePage
     */
    protected function getNewPage(): WorkerHomePage
    {
        return new WorkerHomePage($this->HTMLUtility);
    }

    /**
     * @return $this
     */
    public function addNews(): self
    {
        $news = (new SettingFactory( $this->db, $this->messages ))->getSetting( 'news_text' ) ?? '';

        if ( !empty( $news ) ) {
            $this->page->news = $news;
        }
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function addWorkerHoursSummary(): self
    {
        $timeClockRecordThisWeek = (new WorkerTimeClockRecord(
            $this->HTMLUtility,
            $this->format,
            $this->messages
        ))->init( $this->user->shifts, $this->user->name );

        $timeClockRecordThisWeek->extractData();

        $this->page->workerHoursTable = $this->HTMLUtility::getTableHTML( [
            'data' => [[
                'had_lunch_today' => $this->user->hadLunchToday() ? 'Yes' : 'No',
                'hours_today' => $timeClockRecordThisWeek->getTotalHoursToday(),
                'hours_this_week' => $timeClockRecordThisWeek->getTotalHoursThisWeek()
            ]],
            'columns' => [
                'had_lunch_today' => 'Had Lunch Today?',
                'hours_today' => 'Total Hours Today',
                'hours_this_week' => 'Total Hours This Week'
            ],
            'class' => 'mb-0'
        ] );
        return $this;
    }


    /**
     * @return $this
     */
    public function addReports(): self
    {
        $user = $this->user;
        $format = $this->format;
        $htmlUtility = $this->HTMLUtility;

        $shiftsCurrent = $user->shifts->getUnfinishedShifts();
        $this->page->reports = [
            'current_shift_table' => (new WorkerHomeShiftTable(
                $htmlUtility,
                $format,
                $this->messages
            ))->init( $shiftsCurrent )
                ->setNoShiftsMessage( 'You are not currently clocked into any shifts.' )
                ->setTitle( 'Your Current ' . ucfirst( $shiftsCurrent->getPluralOrSingular() ) ),
            'shift_latest_table' => (new WorkerHomeShiftTable(
                $htmlUtility,
                $format,
                $this->messages
            ))->init(
                $user->shifts->getLastWorkedShifts( 5 )
            )
                ->setNoShiftsMessage( 'No recent shifts found.' )
                ->setTitle( 'Your Recent Shifts' ),
            'time_clock_record' => (new WorkerTimeClockRecord(
                $htmlUtility,
                $format,
                $this->messages
            ))->init(
                $user->shifts,
                $user->name,
                $this->startDate
            )
        ];
        return $this;
    }


    /**
     * @return $this
     */
    public function addActionButtons(): self
    {
        $class = 'btn btn-lg btn-block mb-3';
        $user = $this->user;
        if ( !empty($user->healthCheck() )) {
            $this->page->actions = $this->messages->getMessageHTML( 'No actions available due to error.', 'warning', false );
            return $this;
        }
        $todayShifts = $user->shifts->getShiftsToday();
        $startShiftText = $todayShifts->getCount() === 0 ? 'Start Day' : 'Next Shift';
        $actionButtons = [
            [
                'class' => $class . ' btn-success',
                'element' => 'a',
                'content' => $startShiftText,
                'href' => 'worker.php?choose=job',
                'disabled' => true
            ]
        ];
        if ( $user->hadLunchToday() ) {
            $actionButtons[] = [
                'class' => $class . ' btn-danger',
                'type' => 'button',
                'content' => 'Finish Day'
            ];
        } elseif ( $todayShifts->getCount() > 0 ) {
            $actionButtons[] = [
                'class' => $class . ' btn-primary',
                'type' => 'button',
                'content' => 'Start Lunch'
            ];
        }
        $actions = '';
        foreach ( $actionButtons as $button ) {
            $actions .= $this->HTMLUtility::getButton( $button );
        }
        $this->page->actions = $actions;
        return $this;
    }


}