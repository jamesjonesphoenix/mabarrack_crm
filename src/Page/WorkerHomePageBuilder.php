<?php

namespace Phoenix\Page;

use Phoenix\Utility\DateTimeUtility;
use Phoenix\Entity\SettingFactory;
use Phoenix\Entity\ShiftFactory;
use Phoenix\Report\Archive\ArchiveTableShiftsWorkerHome;
use Phoenix\Report\Worker\WorkerTimeClockRecord;

/**
 * Class WorkerPageBuilder
 *
 * @author James Jones
 * @property WorkerHomePage $page
 *
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
        $this->addWorkerShiftsTables();
        $this->addTimeClockRecord();


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
        ))->setStartAndFinishDates( $this->startDate )
            ->setShifts( $this->user->shifts );
        $timeClockRecordThisWeek->getData();

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
     * @throws \Exception
     */
    public function addTimeClockRecord(): self
    {
        $this->page->addContent(
            (new WorkerTimeClockRecord(
                $this->HTMLUtility,
                $this->format
            ))
                ->setStartAndFinishDates( $this->startDate )
                ->setUsername( $this->user->name )
                ->setShifts( $this->user->shifts )
                ->render()
        );
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function addWorkerShiftsTables(): self
    {
        // d($this->entities);

        $shiftsCurrent = $this->user->shifts->getUnfinishedShifts();

        $dummyShift = (new ShiftFactory( $this->db, $this->messages ))->getNew();

        $currentShiftArchive = (new ArchiveTableShiftsWorkerHome(
            $this->HTMLUtility,
            $this->format,
        ))
            ->setEntities( $shiftsCurrent->getAll(), $dummyShift )
            ->setTitle( 'Your Current ' . ucfirst( $shiftsCurrent->getPluralOrSingular() ) )
            ->setEmptyMessageClass( 'info' )
            ->setEmptyMessage( 'You are not currently clocked into any shifts.' )
            ->removeErrors();

        $shiftLatestArchive = (new ArchiveTableShiftsWorkerHome(
            $this->HTMLUtility,
            $this->format,
        ))
            ->setEntities( $this->user->shifts->getLastWorkedShifts( 5 )->getAll(), $dummyShift )
            ->setTitle( 'Your Most Recent Shifts' )
            ->setEmptyMessage( 'No recent shifts found.' )
            ->removeErrors();


        $currentShiftArchive->getData(); // hackish - extractData() now run twice
        $shiftLatestArchive->getData(); //hackish - extractData() now run twice
        if ( $shiftsCurrent->getCount() ) {
            $shiftLatestArchive->disableColumnToggles();
        }
        $columnsOne = $currentShiftArchive->getColumns( 'is_empty' );
        $columnsTwo = $shiftLatestArchive->getColumns( 'is_empty' );
        if ( $columnsOne !== $columnsTwo ) {
            foreach ( $columnsOne as $columnID => $isEmpty ) {
                if ( empty( $columnsTwo[$columnID] ) ) {
                    $currentShiftArchive->editColumn( $columnID, ['is_empty' => false] );
                }
            }
            foreach ( $columnsTwo as $columnID => $columnArgs ) {
                if ( empty( $columnsOne[$columnID] ) ) {
                    $shiftLatestArchive->editColumn( $columnID, ['is_empty' => false] );
                }
            }
        }

        $this->page->addContent( $currentShiftArchive->render() . $shiftLatestArchive->render() );
        return $this;
    }


    /**
     * @return $this
     */
    public function addActionButtons(): self
    {
        $class = 'btn btn-lg btn-block ';
        $user = $this->user;
        if ( !empty( $user->healthCheck() ) ) {
            $this->page->setActions( $this->HTMLUtility::getAlertHTML( 'No actions available due to error.', 'warning', false ) );
            return $this;
        }
        $todayShifts = $user->shifts->getShiftsToday();
        $unfinishedShift = $user->shifts->getUnfinishedShifts()->getOne();

      //  $currentTime = DateTimeUtility::roundTime( date( 'H:i' ) ); //get current time
     //   $cutOffTime = '17:00';
     //   if(){

      //  }

        if ( $todayShifts->getCount() === 0 ) {
            $startShiftText = 'Start Day';
        } else {
            $startShiftText = $unfinishedShift !== null ? 'Next Shift' : 'Start New Shift';
        }
        //$startShiftText = $todayShifts->getCount() === 0 ? 'Start Day' : 'Next Shift';
        $actionButtons[] = [
            'class' => $class . ' btn-success',
            'element' => 'a',
            'content' => $startShiftText,
            'href' => 'worker.php?choose=job',
            'disabled' => true
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