<?php


namespace Phoenix\Entity;

use Phoenix\Utility\DateTimeUtility;

/**
 * @author James Jones
 * @property Shift[] $entities
 * @method Shift[] getAll()
 * @method Shift getOne(int $id = null)
 *
 * Class Shifts
 *
 * Helper methods for manipulating arrays of Shift() instances
 *
 * @package Phoenix\Entity
 *
 */
class Shifts extends Entities
{
    /**
     * @var bool
     */
    private bool $ordered = false;

    /**
     * @var array
     */
    private array $workerCost = [];

    /**
     * @var array
     */
    private array $workerMinutes = [];

    /**
     * @return Shifts
     */
    public function getShiftsToday(): Shifts
    {
        $today = date( 'Y-m-d' );
        return $this->getShiftsOverTimespan( $today, $today );
    }

    /**
     * @param string $dateStart
     * @param string $dateFinish
     * @return Shifts
     */
    public function getShiftsOverTimespan(string $dateStart = '', string $dateFinish = ''): Shifts
    {
        if ( empty( $dateStart ) || empty( $dateFinish ) ) {
            return new self();
        }
        foreach ( $this->entities as $shift ) {
            if ( DateTimeUtility::isAfter( $shift->date, $dateStart, true )
                && DateTimeUtility::isBefore( $shift->date, $dateFinish, true ) ) {
                $shifts[$shift->id] = $shift;
            }
        }
        return new self( $shifts ?? [] );
    }

    /**
     * Calculates value of work over a date range as a percentage of all work value
     *
     * @param string $dateStart
     * @param string $dateFinish
     * @return float
     */
    public function calculateCompletionOverPeriod($dateStart = '', $dateFinish = ''): float
    {
        $periodWorkerCost = 0;
        $totalWorkerCost = 0;
        foreach ( $this->entities as $shift ) {
            if ( DateTimeUtility::isAfter( $shift->date, $dateStart, true )
                && DateTimeUtility::isBefore( $shift->date, $dateFinish, false ) ) {
                $periodWorkerCost += $shift->getShiftCost();
            }
            $totalWorkerCost += $shift->getShiftCost();
        }
        if ( empty( $totalWorkerCost ) ) { //equals 0 - empty() accounts for integer or float 0
            return 1;
        }
        return $periodWorkerCost / $totalWorkerCost;
    }

    /**
     * @return string
     */
    public function getEarliestShift(): string
    {
        $this->orderLatestToEarliest();
        return $this->entities[array_key_first( $this->entities )]->date;
    }

    /**
     * @return Shift|null
     */
    public function getLatestNonLunchShift(): ?Shift
    {
        $this->orderLatestToEarliest();

        foreach($this->entities  as $shift){
            if($shift->activity->id !== 0){
                return $shift;
            }
        }
        return null;
        // return $this->entities[array_key_last( $this->entities )]->date;
    }

    /**
     * @return Shifts
     */
    public function orderLatestToEarliest(): Shifts
    {
        if ( count( $this->entities ) <= 1 ) {
            return $this;
        }
        if ( $this->ordered ) {
            return $this;
        }
        $stringToTimes = [];
        $shiftsByDate = [];
        foreach ( $this->entities as $shift ) {
            $stringToTimes[$shift->date] ??= strtotime( $shift->date );
            $dateToTime = $stringToTimes[$shift->date];
            $stringToTimes[$shift->timeFinished] ??= strtotime( $shift->timeFinished );
            $timeFinishedToTime = $stringToTimes[$shift->timeFinished];
            $shiftsByDate[$dateToTime][$timeFinishedToTime][] = $shift; //array for each time because sometimes shifts finish at the same time
        }
        krsort( $shiftsByDate );
        foreach ( $shiftsByDate as $shiftsByTime ) {
            krsort( $shiftsByTime );
            foreach ( $shiftsByTime as $shifts ) {
                foreach ( $shifts as $shift ) {
                    $sortedShifts[$shift->id] = $shift;
                }
            }
        }
        $this->entities = $sortedShifts ?? [];
        $this->ordered = true;
        return $this;
    }

    /**
     * @param int $numberOfShifts
     * @return Shifts
     */
    public function getLastWorkedShifts(int $numberOfShifts = 1): Shifts
    {
        $this->orderLatestToEarliest();

        //$sdfs = $this->entities ;
        // d(current($sdfs));

        foreach ( $this->entities as $shiftID => $shift ) {

            if ( !empty( $shift->timeFinished ) ) {
                $returnShifts[$shiftID] = $shift;
                if ( count( $returnShifts ) >= $numberOfShifts ) {
                    break;
                }
            }
        }
        return new self( $returnShifts ?? [] );
    }


    /**
     * Returns array of unfinished shifts. It should only return a single shift in normal circumstances
     *
     * @return Shifts
     */
    public function getUnfinishedShifts(): Shifts
    {

        foreach ( $this->entities as $shift ) {
            if ( empty( $shift->timeFinished ) ) {
                $returnShifts[$shift->id] = $shift;
            }
        }
        return (new self( $returnShifts ?? [] ))->orderLatestToEarliest();
    }

    /**
     * @return Shifts
     */
    public function getFinishedShifts(): Shifts
    {
        foreach ( $this->entities as $shift ) {
            if ( !empty( $shift->timeFinished ) ) {
                $returnShifts[$shift->id] = $shift;
            }
        }
        return (new self( $returnShifts ?? [] ));
    }


    /**
     * Type is is activity type
     *
     * @param string $type
     * @return int
     */
    public function getWorkerMinutes(string $type = ''): int
    {
        if ( !empty( $this->workerMinutes[$type] ) ) {
            return $this->workerMinutes[$type];
        }
        $workerMinutes = 0;
        foreach ( $this->getShiftsOfType( $type ) as $shift ) {
            $workerMinutes += $shift->getShiftLength();
        }
        return $this->workerMinutes[$type] = $workerMinutes;
    }

    /**
     * @return int
     */
    public function getTotalWorkerMinutes(): int
    {
        return $this->getWorkerMinutes( 'total' );
    }

    /**
     * @param string $type
     * @return float
     */
    public function getWorkerCost(string $type = ''): float
    {
        if ( !empty( $this->workerCost[$type] ) ) {
            return $this->workerCost[$type];
        }
        $workerCost = 0;
        foreach ( $this->getShiftsOfType( $type ) as $shift ) {
            $workerCost += $shift->getShiftCost();
        }
        return $this->workerCost[$type] = $workerCost;
    }

    /**
     * @return float
     */
    public function getTotalWorkerCost(): float
    {
        return $this->getWorkerCost( 'total' );
    }

    /**
     * @return string
     */
    public function getPluralOrSingular(): string
    {
        if ( $this->getCount() > 1 ) {
            return 'shifts';
        }
        return 'shift';
    }

    /**
     * @param string $type
     * @return Shift[]
     */
    private function getShiftsOfType(string $type = ''): array
    {
        if ( empty( $type ) || $type === 'total' ) {
            return $this->entities;
        }
        foreach ( $this->entities as $shift ) {

            if ( $shift->activity->type === $type ) {
                $returnShifts[$shift->id] = $shift;
            }
        }
        return $returnShifts ?? [];
    }


}