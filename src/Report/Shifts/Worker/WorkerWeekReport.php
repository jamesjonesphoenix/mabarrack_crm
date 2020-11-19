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
    protected bool $printButton = true;

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
                'url' => $url->setQueryArg( 'date_start', $this->getDatePrevious() )->write(),
                'text' => 'Previous Week'
            ], [
                'url' => $url->setQueryArg( 'date_start', $this->getDateNext() )->write(),
                'text' => 'Next Week'
            ]], parent::getNavLinks()
        );

    }
}