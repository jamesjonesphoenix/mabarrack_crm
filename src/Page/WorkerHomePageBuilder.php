<?php

namespace Phoenix\Page;

use DateTime;
use Phoenix\Entity\Shift;
use Phoenix\Utility\DateTimeUtility;
use Phoenix\Entity\SettingFactory;
use Phoenix\Entity\ShiftFactory;

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
    private string $dateStart = '';

    /**
     * @param string $dateStart
     * @return $this
     */
    public function setDateStart(string $dateStart = ''): self
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function buildPage(): self
    {
        $this->getReportClient()->getShiftsReportBuilder()->setUser( $this->user );

        $this->page = $this->getNewPage()
            ->setTitle(
                $this->HTMLUtility::getIconHTML('user-clock') . ' ' . $this->user->getNamePossessive(true) . ' Dashboard'
            );
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
        $timeClockRecordThisWeek = $this->getReportClient()->getShiftsReportBuilder()
            ->setDatesForWeek()
            ->getTimeClockRecord();

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
            $this->getReportClient()->getShiftsReportBuilder()
                ->setDatesForWeek( $this->dateStart )
                ->getTimeClockRecord()
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
        $shiftsCurrent = $this->user->shifts->getUnfinishedShifts();
        $dummyShift = (new ShiftFactory( $this->db, $this->messages ))->getNew();

        $currentShiftArchive = $this->getReportClient()->getFactory()->archiveTables()->getShiftsWorkerHome()
            ->setEntities( $shiftsCurrent )
            ->setTitle(
                'Your Current ' . ucfirst( $shiftsCurrent->getPluralOrSingular() )
            )
            ->setEmptyMessageClass( 'info' )
            ->setEmptyMessage(
                'You are not currently clocked into any shifts.'
            )
            ->removeErrors()
            ->setDummyEntity( $dummyShift );

        $shiftLatestArchive = $this->getReportClient()->getFactory()->archiveTables()->getShiftsWorkerHome()
            ->setEntities( $this->user->shifts->getLastWorkedShifts( 5 ) )
            ->setTitle(
                'Your Most Recent Shifts'
            )
            ->setEmptyMessage(
                'No recent shifts found.'
            )
            ->removeErrors()
            ->setDummyEntity( $dummyShift );


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
        $user = $this->user;
        if ( !empty( $user->healthCheck() ) ) {
            $this->page->setActions( $this->HTMLUtility::getAlertHTML( 'No actions available due to error.', 'warning', false ) );
            return $this;
        }
        $todayShifts = $user->shifts->getShiftsToday()->getCount() > 0;
        $unfinishedShift = $user->shifts->getUnfinishedShifts()->getOne();

        $actionButtons = $this->getStartShiftButtons( $todayShifts, $unfinishedShift );
        $actionButtons[] = $this->getFinishButton( $todayShifts, $unfinishedShift );

        foreach ( $actionButtons as $button ) {
            $button['class'] = ($button['class'] ?? '') . ' btn btn-lg btn-block ';
            $button['element'] = 'a';
            $html[] = $this->HTMLUtility::getButton( $button );
        }
        $this->page->setActions( implode( '', $html ?? [] ) );
        return $this;
    }


    /**
     * @param bool       $todayShifts
     * @param Shift|null $unfinishedShift
     * @return array
     * @throws \Exception
     */
    private function getStartShiftButtons(bool $todayShifts, Shift $unfinishedShift = null): array
    {
        $cutoffTime = (new SettingFactory( $this->db, $this->messages ))->getCutoffTime();

        $minutes = 5;
        $fuzzyCutOffTime = (new DateTime( $cutoffTime ))->modify( '-' . $minutes . ' minutes' )->format( 'H:i' );

        $currentTime = date( 'H:i' );

        if ( DateTimeUtility::isAfter( $currentTime, $fuzzyCutOffTime ) ) {
            $shimText = DateTimeUtility::isAfter( $currentTime, $cutoffTime ) ? 'is later than' : 'is less than ' . $minutes . ' minutes from';
            $this->messages->add( "You can't start any more shifts today as the current time "
                . $this->HTMLUtility::getBadgeHTML( $currentTime, 'primary' )
                . ' ' . $shimText . ' the cutoff time'
                . $this->HTMLUtility::getBadgeHTML( $cutoffTime, 'primary' ) . '.',
                'info' );
            return [];
        }
        if ( !$todayShifts ) {
            $startShiftText = 'Start Day';
        } else {
            $startShiftText = $unfinishedShift !== null ? 'Next Shift' : 'Start New Shift';
        }
        $actionButtons[] = [
            'class' => 'btn-success',
            'content' => $startShiftText,
            'href' => 'worker.php?choose=job',
            'disabled' => true
        ];

        if ( ($unfinishedShift === null || $unfinishedShift->activity->id !== 0) && $todayShifts ) {
            $actionButtons[] = [
                'class' => 'btn-primary',
                'content' => 'Start Lunch',
                'href' => $this->user->hadLunchToday() ? 'worker.php?additional_lunch=1' : 'worker.php?job=0&activity=0&next_shift=1',
            ];
        }

        return $actionButtons ?? [];
    }

    /**
     * @param bool       $todayShifts
     * @param Shift|null $unfinishedShift
     * @return array|string[]
     */
    private function getFinishButton(bool $todayShifts, Shift $unfinishedShift = null): array
    {
        if ( !$todayShifts || $unfinishedShift === null ) {  /*$user->hadLunchToday()*/
            return [];
        }
        return [
            'class' => 'btn-danger',
            'content' => $unfinishedShift->activity->id === 0 ? 'Finish Lunch' : 'Clock Off',
            'href' => 'worker.php?finish_day=1',
        ];

    }
}