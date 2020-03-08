<?php

namespace Phoenix;

/**
 * @method Shift[] addOneToOneEntityProperties($entities, $additionFactory, $joinPropertyName = '')
 *
 * Class ShiftFactory
 */
class ShiftFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected $className = 'Shift';

    /**
     * @var string
     */
    protected $tableName = 'shifts';

    /**
     * @return Shift[]
     */
    public function getAllUnfinishedShift(): array
    {
        //$unfinishedShifts = PDOWrap::instance()->getRows( 'shifts', array('time_finished' => null) );
        return $this->getEntities( ['time_finished' => null] );
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

        $lastShift = $this->getShifts( [
            'worker' => $userID,
            'date' => $latestShiftDate,
            'time_finished' => $latestShiftTime,
            'activity' => ['operator' => '!=', 'value' => 0],
            'job' => ['operator' => '!=', 'value' => 0],
        ] );
        if(empty($lastShift)){
            return null;
        }
        if ( count( $lastShift ) > 1 ) {
            ksort($lastShift);
            $this->messages->add( 'There should only be one last worked shift but multiple found.' );
        }
        return array_shift( $lastShift );
    }

    /**
     * Gets the shift a worker is currently clocked on to.
     *
     * @param int $userID
     * @return Shift|bool
     */
    public function getWorkerUnfinishedShift(int $userID = 0)
    {
        $previousShift = $this->getEntities( [
            'worker' => $userID,
            'time_finished' => null
        ], true );
        if ( count( $previousShift ) > 1 ) {
            $this->messages->add( 'Found more than one unfinished shift. Should only be one.' );
            return false;
        }
        return array_shift( $previousShift );
    }

    /**
     * Alias for getEntities()
     *
     * @param int $id
     * @return Shift
     */
    public function getShift(int $id = 0): Shift
    {
        return $this->getEntities( ['ID' => $id], true )[$id];
    }

    /**
     * Alias for getEntities()
     *
     * @param array $queryArgs
     * @param bool $provision
     * @return Shift[]
     */
    public function getShifts(array $queryArgs = [], $provision = false): array
    {
        return $this->getEntities( $queryArgs, $provision );
    }



    /**
     * @param array $queryArgs
     * @param bool $provision
     * @return Shift[]
     */
    public function getEntities(array $queryArgs = [], $provision = false): array
    {
        $shifts = $this->getClassesFromDBWrapper( $queryArgs );
        if ( !$provision || empty( $shifts ) ) {
            return $shifts;
        }

        //add job details to each shift to Shift
        $jobFactory = new JobFactory( $this->db, $this->messages );
        $shifts = $this->addOneToOneEntityProperties( $shifts, $jobFactory );


        //add furniture to each shift
        $shifts = $this->addFurniture( $shifts );

        //add workers details to each shift to Shift
        $userFactory = new UserFactory( $this->db, $this->messages );
        $shifts = $this->addOneToOneEntityProperties( $shifts, $userFactory, 'worker' );

        //add activities to each shift to Shift
        $activityFactory = new ActivityFactory( $this->db, $this->messages );
        $shifts = $this->addOneToOneEntityProperties( $shifts, $activityFactory );

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
    private function addFurniture(array $shifts = []): array
    {
        $furnitureIDs = $this->getEntityIDs( $shifts, 'furniture' );
        if ( empty( $furnitureIDs ) ) {
            return $shifts;
        }
        $furnitureFactory = new FurnitureFactory( $this->db, $this->messages );
        $furniture = $furnitureFactory->getFurniture( ['ID' => [
            'operator' => 'IN',
            'value' => $furnitureIDs
        ]] );

        foreach ( $shifts as &$shift ) {
            if ( $shift->furniture === 0 ) {
                continue;
            }
            $jobFurniture = $shift->job->furniture;
            if ( !empty( $jobFurniture[$shift->furniture] ) ) {
                $furnitureClass = $furniture[$shift->furniture];
            } elseif ( count( $jobFurniture ) === 1 ) {
                $shift->furniture = key( $jobFurniture );
                $furnitureClass = $furnitureFactory->getOneFurniture( $shift->furniture );
            } else {
                continue;
            }

            $furnitureQuantity = $jobFurniture[$shift->furniture]['Quantity'] ?? $jobFurniture->quantity ?? 0;
            $furnitureClass->quantity = $furnitureQuantity;
            $shift->furniture = $furnitureClass;
        }
        return $shifts;
    }

    /**
     * @return Shift
     */
    protected function instantiateEntityClass(): Entity
    {
        return new Shift( $this->db, $this->messages );
    }

    /**
     * @param array $queryArgs
     * @return Shift[]
     */
    protected function getClassesFromDBWrapper(array $queryArgs = []): array
    {
        return $this->instantiateEntitiesFromDB( $queryArgs );
    }
}