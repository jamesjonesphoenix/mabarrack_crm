<?php


namespace Phoenix\Report\Worker;


use Phoenix\DateTimeUtility;
use Phoenix\Entity\Shift;
use Phoenix\Entity\Shifts;
use Phoenix\Entity\User;
use Phoenix\Report\Report;

/**
 * Class WorkerReport
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
abstract class WorkerReport extends Report
{
    /**
     * @var User
     */
    protected User $user;

    /**
     * @var Shifts
     */
    protected Shifts $shifts;

    /**
     * @var string
     */
    private string $dateStart;

    /**
     * @var string
     */
    private string $dateFinish;

    /**
     * @param Shifts $shifts
     * @param string $userName
     * @param string $dateStart
     * @return WorkerReport
     */
    public function init(Shifts $shifts, string $userName = '', string $dateStart = ''): WorkerReport
    {
        $this->setStartAndFinishDates( $dateStart );
        $this->setTitle( $userName );
        $this->shifts = $shifts->getFinishedShifts()->getShiftsOverTimespan(
            $this->getDateStart(),
            $this->getDateFinish()
        );
        return $this;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setTitle(string $username = ''): self
    {
        $username = !empty( $username ) ? $username . ' - ' : '';
        $this->title = $username . $this->title . ' <small>- ' . $this->getDateStart() . ' to ' . $this->getDateFinish() . '</small>';
        return $this;
    }

    /**
     * @param string $dateStart
     * @return string
     */
    public
    function getDateStart(string $dateStart = ''): string
    {
        if ( !empty( $this->dateStart ) ) {
            return $this->dateStart;
        }
        if ( !empty( $dateStart ) ) {
            return $this->dateStart = $dateStart;
        }
        return '';
    }

    /**
     * @param string $dateFinish
     * @return string
     */
    public
    function getDateFinish(string $dateFinish = ''): string
    {
        if ( !empty( $this->dateFinish ) ) {
            return $this->dateFinish;
        }
        if ( !empty( $dateFinish ) ) {
            return $this->dateFinish = $dateFinish;
        }
        return '';
    }

    /**
     * @param string $dateStart
     * @return bool
     */
    public function setStartAndFinishDates($dateStart = ''): bool
    {
        $dateFormat = 'd-m-Y'; /*Get week dates*/

        if ( !empty( $dateStart ) && DateTimeUtility::timeDifference( date( 'Y-m-d' ), $dateStart ) !== 0 ) { /*Date provided and not today*/
            $this->dateStart = $dateStart;
            $this->dateFinish = date( $dateFormat, strtotime( $dateStart . ' + 6 days' ) );
            return true;
        }

        /*Date not provided*/
        $weekDay = date( 'w' );
        $dateStartTimestamp = $weekDay === '5' /*Friday*/ ? time() : strtotime( 'previous friday' );
        $this->dateStart = date( $dateFormat, $dateStartTimestamp );

        $dateFinishTimestamp = $weekDay === '4' /*Thursday*/ ? time() : strtotime( 'next thursday' );
        $this->dateFinish = date( $dateFormat, $dateFinishTimestamp );
        return true;
    }

    /**
     * @return string
     */
    public
    function getDateNext(): string
    {
        if ( !empty( $this->dateStart ) ) {
            return date( 'd-m-Y', strtotime( $this->getDateStart() . ' + 7 days' ) );
        }
        return '';
    }

    /**
     * @return string
     */
    public
    function getDatePrevious(): string
    {
        if ( !empty( $this->dateStart ) ) {
            return date( 'd-m-Y', strtotime( $this->getDateStart() . ' - 7 days' ) );
        }
        return '';
    }
}