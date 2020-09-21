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
 * @property WorkerHomePage $page
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class WorkerHomePageBuilder extends WorkerPageBuilder
{
    /**
     * @var string
     */
    private string $startDate = '';

    /**
     * @param string $startDate
     * @return $this
     */
    public function setStartDate(string $startDate = ''): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage()->setTitle( $this->user->getNamePossessive() . ' Dashboard' );
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
        return new WorkerHomePage( $this->HTMLUtility );
    }

    /**
     * @return $this
     */
    public function addNews(): self
    {
        $news = (new SettingFactory( $this->db, $this->messages ))->getSetting( 'news_text' ) ?? '';
        if ( empty( $news ) ) {
            $news = 'No news right now.';
        }
        $this->page->setNews(
            $this->HTMLUtility::getAlertHTML( $news, 'info', false )
        );
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
            $this->format
        ))->init( $this->user->shifts, $this->user->name );

        $timeClockRecordThisWeek->extractData();

        $this->page->setWorkerHoursTable( $this->HTMLUtility::getTableHTML( [
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
        ] ) );
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

        foreach ( [
                      'current_shift_table' => (new WorkerHomeShiftTable(
                          $htmlUtility,
                          $format,
                      ))->init( $shiftsCurrent )
                          ->setNoShiftsMessage( 'You are not currently clocked into any shifts.' )
                          ->setTitle( 'Your Current ' . ucfirst( $shiftsCurrent->getPluralOrSingular() ) ),


                      'shift_latest_table' => (new WorkerHomeShiftTable(
                          $htmlUtility,
                          $format,
                      ))->init(
                          $user->shifts->getLastWorkedShifts( 5 )
                      )
                          ->setNoShiftsMessage( 'No recent shifts found.' )
                          ->setTitle( 'Your Recent Shifts' ),


                      'time_clock_record' => (new WorkerTimeClockRecord(
                          $htmlUtility,
                          $format,
                      ))->init(
                          $user->shifts,
                          $user->name,
                          $this->startDate
                      )
                  ] as $report ) {
            $this->page->addContent( $report->render() );
        }
        return $this;
    }


    /**
     * @return $this
     */
    public function addActionButtons(): self
    {
        $class = 'btn btn-lg btn-block mb-3';
        $user = $this->user;
        if ( !empty( $user->healthCheck() ) ) {
            $this->page->setActions( $this->HTMLUtility::getAlertHTML( 'No actions available due to error.', 'warning', false ) );
            return $this;
        }
        $todayShifts = $user->shifts->getShiftsToday();
        $unfinishedShift = $user->shifts->getUnfinishedShifts()->getOne();
        if ( $todayShifts->getCount() === 0 ) {
            $startShiftText = 'Start Day';
        } else {
            $startShiftText = $unfinishedShift !== null ? 'Next Shift' : 'Start New Shift';
        }
        //$startShiftText = $todayShifts->getCount() === 0 ? 'Start Day' : 'Next Shift';
        $actionButtons = [
            [
                'class' => $class . ' btn-success',
                'element' => 'a',
                'content' => $startShiftText,
                'href' => 'worker.php?choose=job',
                'disabled' => true
            ]
        ];

        //if ( $unfinishedShift !== null && $unfinishedShift->activity->id !== 0 && $todayShifts->getCount() > 0 ) {
        if ( ($unfinishedShift === null || $unfinishedShift->activity->id !== 0) && $todayShifts->getCount() > 0 ) {
            $href = $this->user->hadLunchToday() ? 'worker.php?additional_lunch=1' : 'worker.php?job=0&activity=0&next_shift=1';
            $actionButtons[] = [
                'class' => $class . ' btn-primary',
                'element' => 'a',
                'content' => 'Start Lunch',
                'href' => $href,
            ];
        }
        if ( $unfinishedShift !== null && $todayShifts->getCount() > 0  /*$user->hadLunchToday()*/ ) {
            $content = $unfinishedShift->activity->id === 0 ? 'Finish Lunch' : 'Clock Off';
            $actionButtons[] = [
                'class' => $class . ' btn-danger',
                'element' => 'a',
                'content' => $content,
                'href' => 'worker.php?finish_day=1',

            ];
        }

        $actions = '';
        foreach ( $actionButtons as $button ) {
            $actions .= $this->HTMLUtility::getButton( $button );
        }
        $this->page->setActions( $actions );
        return $this;
    }


}