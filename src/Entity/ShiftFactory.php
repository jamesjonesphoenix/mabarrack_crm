<?php

namespace Phoenix\Entity;

/**
 * @method Shift[] addOneToOneEntityProperties(array $entities = [], EntityFactory $additionFactory = null, $provisionArgs = false, string $joinPropertyName = '')
 * @method Shift getEntity(int $id = 0)
 * @method Shift[] getEntities(array $queryArgs = [], $provision = false)
 * @method Shift[] getAll()
 *
 * Class ShiftFactory
 */
class ShiftFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected string $entityName = 'shift';

    /**
     * @return Shift
     */
    protected function instantiateEntityClass(): Shift
    {
        return new Shift( $this->db, $this->messages );
    }

    /**
     * @return Shifts
     */
    public function getAllUnfinishedShift(): Shifts
    {
        return new Shifts(
            $this->getEntities( ['time_finished' => null], true )
        );
    }

    /**
     * Returns last shift a worker worked. Doesn't include Lunch or Factory Jobs
     *
     * @param int $userID
     * @return Shift
     */
    public function getLastWorkedShift(int $userID = 0): ?Shift
    {
        $latestShiftDate = $this->db->run(
            'SELECT MAX(date) FROM shifts WHERE worker=:worker AND activity!=0 AND job!=0',
            [
                'worker' => $userID
            ]
        )->fetch()['MAX(date)'];
        $latestShiftTime = $this->db->run(
            'SELECT MAX(time_finished) FROM shifts WHERE worker=:worker AND activity!=0 AND job!=0 AND date=:date',
            [
                'worker' => $userID,
                'date' => $latestShiftDate
            ]
        )->fetch()['MAX(time_finished)'];

        $lastShift = $this->getEntities( [

            'worker' => $userID,
            'date' => $latestShiftDate,
            'time_finished' => $latestShiftTime,
            'activity' => ['operator' => '!=', 'value' => 0],
            'job' => ['operator' => '!=', 'value' => 0],

        ] );

        if ( empty( $lastShift ) ) {
            return null;
        }
        if ( count( $lastShift ) > 1 ) {
            ksort( $lastShift );
            $this->messages->add( 'There should only be one last worked shift but multiple found.' );
        }
        return array_shift( $lastShift );
    }

    /**
     * Gets the shift a worker is currently clocked on to.
     *
     * @param int|null $userID if null it will get any unfinished shifts
     * @return Shift[]
     */
    public function getWorkerUnfinishedShifts(int $userID = null): array
    {
        $criteria = ['time_finished' => null];
        if ( $userID !== null ) {
            $criteria['worker'] = $userID;
        }
        return $this->getEntities( $criteria, true );
    }

    /**
     * @return Shift
     */
    public function getNew(): Entity
    {
        $shift = $this->instantiateEntityClass();
        $shift->date = date( 'Y-m-d' );
        return $shift;
    }

    /**
     * @param Shift[]    $shifts
     * @param bool|array $provision
     * @return Shift[]
     */
    public function provisionEntities(array $shifts = [], $provision = false): array
    {
        if ( $this->canProvision( $provision, 'job' ) ) { //add job details to each shift to Shift
            $shifts = $this->addOneToOneEntityProperties( $shifts, new JobFactory( $this->db, $this->messages ), $provision['job'] ?? false );
        }
        if ( $this->canProvision( $provision, 'furniture' ) ) { //add furniture to each shift. We must have populated the shifts with Job instances to be able to obtain quantity of shift furniture.
            if ( (!empty( $provision['job'] ) && $provision['job'] === true) || !empty( $provision['job']['furniture'] ) ) {
                $shifts = $this->addFurnitureFromJobs( $shifts );
            } else {
                $shifts = $this->addOneToOneEntityProperties( $shifts, new FurnitureFactory( $this->db, $this->messages ), $provision['furniture'] ?? false );
            }
        }
        // * @method Shift[] addOneToOneEntityProperties($entities, $additionFactory, $joinPropertyName = '')
        if ( $this->canProvision( $provision, 'worker' ) ) { //add workers details to each shift to Shift
            $shifts = $this->addOneToOneEntityProperties( $shifts, new UserFactory( $this->db, $this->messages ), $provision['worker'] ?? false, 'worker' );
        }
        if ( $this->canProvision( $provision, 'activity' ) ) { //add activities to each shift to Shift
            $shifts = $this->addOneToOneEntityProperties( $shifts, new ActivityFactory( $this->db, $this->messages ) );
        }
        return $shifts;
    }

    /**
     * Add Furniture class to shifts including quantity property.
     * Quantity must come from Job class.
     * Jobs include multiple types of furniture. Individual shifts include one type of furniture only so some processing to be done
     *
     * @param Shift[] $shifts
     * @return Shift[]
     */
    private function addFurnitureFromJobs(array $shifts = []): array
    {
        foreach ( $shifts as $shift ) {
            if ( $shift->furniture->id === null ) {
                continue;
            }
            $shiftJobFurniture = $shift->job->furniture;
            if ( !empty( $shiftJobFurniture[$shift->furniture->id] ) ) {
                $shift->furniture = $shift->job->furniture[$shift->furniture->id];
            } else {
                $jobs[$shift->job->id] = $shift->job;
            }
        }
        if ( empty( $jobs ) ) {
            return $shifts;
        }

        //$jobs = (new JobFactory( $this->db, $this->messages ))->addFurniture( $jobs );
        $furnitureIDs = $this->getEntityIDs( $shifts, 'furniture' );
        if ( empty( $furnitureIDs ) ) {
            return $shifts;
        }
        $furnitureInstances = (new FurnitureFactory( $this->db, $this->messages ))->getEntities( ['ID' => [
            'operator' => 'IN',
            'value' => $furnitureIDs
        ]] );
        foreach ( $shifts as $shift ) {
            $shiftFurnitureID = $shift->furniture->id ?? null;
            if ( is_int( $shiftFurnitureID ) && !empty( $furnitureInstances[$shiftFurnitureID] ) ) {
                $shift->furniture = $furnitureInstances[$shiftFurnitureID];
                $shiftJobFurniture = $jobs[$shift->job->id]->furniture;

                if ( is_array( $shiftJobFurniture ) ) { //Need to check if shift furniture actually exists in Job.
                    $shift->job->furniture = $shiftJobFurniture;
                    if ( !empty( $shiftJobFurniture[$shift->furniture->id] ) ) { //Need to check if shift furniture actually exists in Job.
                        $shift->furniture->quantity = $shiftJobFurniture[$shift->furniture->id]->quantity;
                    }
                }
            }
        }

        return $shifts;
    }
}