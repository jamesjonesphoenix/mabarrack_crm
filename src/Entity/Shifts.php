<?php


namespace Phoenix\Entity;

/**
 * @property Shift[] $entities
 * @method Shift[] getAll()
 * @method Shift getOne()
 *
 * Class Shifts
 *
 * Helper methods for manipulating arrays of Shift() instances
 *
 * @author James Jones
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
        $dateStarted = date_create( $dateStart );
        $dateFinished = date_create( $dateFinish );

        foreach ( $this->entities as $shift ) {
            $dateShift = date_create( $shift->date );
            //if($shift->id === 21727){}
            if ( $dateShift
                && (integer)$dateStarted->diff( $dateShift )->format( '%R%a' ) >= 0
                && (integer)$dateShift->diff( $dateFinished )->format( '%R%a' ) >= 0 ) {
                $shifts[$shift->id] = $shift;
            }
        }
        return new self( $shifts ?? [] );

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
            $shiftsByDate[$dateToTime][$timeFinishedToTime] = $shift;
        }
        krsort( $shiftsByDate );
        foreach ( $shiftsByDate as $shiftsByTime ) {
            krsort( $shiftsByTime );
            foreach ( $shiftsByTime as $shiftByTime ) {
                $sortedShifts[$shiftByTime->id] = $shiftByTime;
            }
        }
        $this->entities = $sortedShifts ?? [];
        $this->ordered = true;
        return $this;
    }

    /**
     * @param int $numberOfShifts
     * @return Shifts|null
     */
    public function getLastWorkedShifts(int $numberOfShifts = 1): ?Shifts
    {
        $this->orderLatestToEarliest();
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
     * @param Shift $shift
     * @return array
     */
    public function addOrReplaceShift(Shift $shift): array
    {
        $shifts = $this->entities;
        if ( empty( $shifts[$shift->id] ) ) {
            return [$shift->id => $shift] + $shifts;
        }
        $shifts[$shift->id] = $shift;
        return $shifts;
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
     * @return int
     */
    public function getCount(): int
    {
        return count( $this->entities );
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

    public function getPluralOrSingular(): string
    {
        if ( $this->getCount() === 1 ) {
            return 'shift';
        }
        return 'shifts';
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
            if ( !empty( $shift->activity->type ) && $shift->activity->type === $type ) {
                $returnShifts[$shift->id] = $shift;
            }
        }
        return $returnShifts ?? [];
    }

}