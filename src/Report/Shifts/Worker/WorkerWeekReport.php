<?php


namespace Phoenix\Report\Shifts\Worker;

use Phoenix\Entity\User;
use Phoenix\Report\Shifts\ShiftsReport;

/**
 * Class WorkerWeekReport
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
abstract class WorkerWeekReport extends ShiftsReport
{
    /**
     * @var User
     */
    protected User $user;

    /**
     * @var string
     */
    private string $dateStart;

    /**
     * @var bool
     */
    protected bool $includePrintButton = true;

    /**
     * @param string $date
     * @return $this
     */
    public
    function setDateStart(string $date = ''): self
    {
        $this->dateStart = $date;
        return $this;
    }

    /**
     * @return string
     */
    public
    function getDateStart(): string
    {
        return $this->dateStart ?? '';
    }

    /**
     * @return string
     */
    public
    function getDateNext(): string
    {
        if ( !empty( $this->dateStart ) ) {
            return date( 'Y-m-d', strtotime( $this->getDateStart() . ' + 7 days' ) );
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
            return date( 'Y-m-d', strtotime( $this->getDateStart() . ' - 7 days' ) );
        }
        return '';
    }

    /**
     * @return array
     */
    public function getNavLinks(): array
    {
        $url = $this->getURL()->removeQueryArg( 'date_finish' );
        return array_merge( [
            [
                'href' => $url->setQueryArg( 'date_start', $this->getDatePrevious() )->write(),
                'content' => 'Previous Week'
            ], [
                'href' => $url->setQueryArg( 'date_start', $this->getDateNext() )->write(),
                'content' => 'Next Week'
            ]], parent::getNavLinks()
        );

    }
}