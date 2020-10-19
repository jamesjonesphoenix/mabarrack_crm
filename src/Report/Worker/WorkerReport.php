<?php


namespace Phoenix\Report\Worker;


use Phoenix\Utility\DateTimeUtility;
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
     * @var bool
     */
    protected bool $printButton = true;

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
        $username = !empty( $this->username ) ? $this->htmlUtility::getBadgeHTML( $this->username ) . ' ' : '';
        return $username
            . $this->title
            . ' <small>'
            . $this->htmlUtility::getBadgeHTML( $this->getDateStart() )
            . ' to '
            . $this->htmlUtility::getBadgeHTML( $this->getDateFinish() )
            . '</small>';
    }

    /**
     * @return string
     */
    public
    function getDateStart(): string
    {
        return $this->dateStart;
    }

    /**
     * @return string
     */
    public
    function getDateFinish(): string
    {
        return $this->dateFinish;
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
        } else { /*Date not provided*/
            $weekDay = date( 'w' );
            $dateStartTimestamp = $weekDay === '5' /*Friday*/ ? time() : strtotime( 'previous friday' );
            $this->dateStart = date( $dateFormat, $dateStartTimestamp );

            $dateFinishTimestamp = $weekDay === '4' /*Thursday*/ ? time() : strtotime( 'next thursday' );
            $this->dateFinish = date( $dateFormat, $dateFinishTimestamp );
        }
        $this->setEmptyMessageWithDates();
        return $this;
    }

    /**
     *
     */
    public function setEmptyMessageWithDates(): void
    {
        $this->emptyMessage = 'No completed shifts found from'
            . $this->htmlUtility::getBadgeHTML( $this->getDateStart() )
            . ' to '
            . $this->htmlUtility::getBadgeHTML( $this->getDateFinish() )
            . ' to report.';
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

    /**
     * @return array
     */
    public function getNavLinks(): array
    {
        $strStart = 'start_date=';
        $url = $_SERVER['REQUEST_URI'];
        if ( empty( parse_url( $url, PHP_URL_QUERY ) ) ) {
            $url .= '?';
        } else {
            $url = str_replace(
                $strStart . $this->getDateStart(),
                '',
                $url );
            $url = trim( $url, '&' );
            if ( substr( $url, -1 ) !== '?' ) {
                $url .= '&';
            }
        }
        $url .= $strStart;
        return array_merge( [
            [
                'url' => $url . $this->getDatePrevious(),
                'text' => 'Previous Week'
            ], [
                'url' => $url . $this->getDateNext(),
                'text' => 'Next Week'
            ]], parent::getNavLinks()
        );

    }
}