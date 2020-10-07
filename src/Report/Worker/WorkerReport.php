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
     * @var string
     */
    private string $username = '';

    /**
     * @param Shifts $shifts
     * @return WorkerReport
     */
    public function setShifts(Shifts $shifts): WorkerReport
    {
        $this->shifts = $shifts
            ->getFinishedShifts()
            ->getShiftsOverTimespan(
                $this->getDateStart(),
                $this->getDateFinish()
            );
        return $this;
    }

    /**
     * @param string $userName
     * @return $this
     */
    public function setUsername(string $userName = ''): self
    {
        $this->username = $userName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        $username = !empty( $this->username  ) ? $this->username  . ' - ' : '';
        return $username . $this->title . ' ' . $this->htmlUtility::getBadgeHTML( $this->getDateStart()) . ' to ' . $this->htmlUtility::getBadgeHTML( $this->getDateFinish());
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
     * @return $this
     */
    public function setStartAndFinishDates($dateStart = ''): self
    {
        $dateFormat = 'd-m-Y'; /*Get week dates*/

        if ( !empty( $dateStart ) && DateTimeUtility::timeDifference( date( 'Y-m-d' ), $dateStart ) !== 0 ) { /*Date provided and not today*/
            $this->dateStart = $dateStart;
            $this->dateFinish = date( $dateFormat, strtotime( $dateStart . ' + 6 days' ) );
            return $this;
        }

        /*Date not provided*/
        $weekDay = date( 'w' );
        $dateStartTimestamp = $weekDay === '5' /*Friday*/ ? time() : strtotime( 'previous friday' );
        $this->dateStart = date( $dateFormat, $dateStartTimestamp );

        $dateFinishTimestamp = $weekDay === '4' /*Thursday*/ ? time() : strtotime( 'next thursday' );
        $this->dateFinish = date( $dateFormat, $dateFinishTimestamp );
        return $this;
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