<?php

namespace Phoenix\Entity;

use Phoenix\Utility\DateTimeUtility;

/**
 *
 * @property Customer    $customer
 * @property string      $dateFinished
 * @property string      $dateStarted
 * @property string      $description
 * @property Furniture[] $furniture
 * @property integer     $priority
 * @property float       $salePrice
 * @property float       $materialCost
 * @property float       $contractorCost
 * @property float       $spareCost
 * @property Shifts      $shifts
 * @property Setting     $status
 *
 * Class Job
 *
 * @package Phoenix
 */
class Job extends Entity
{
    /**
     * @var string Fontawesome icon
     */
    protected string $icon = 'hammer';

    /**
     * @var Customer
     */
    protected Customer $_customer;

    /**
     * @var string
     */
    protected string $_dateStarted;

    /**
     * @var string
     */
    protected string $_dateFinished;

    /**
     * @var string
     */
    protected string $_description;

    /**
     * @var Furniture[]
     */
    protected array $_furniture;

    /**
     * @var integer
     */
    protected int $_priority;

    /**
     * @var float
     */
    protected float $_salePrice;

    /**
     * @var float
     */
    protected float $_materialCost;

    /**
     * @var float
     */
    protected float $_contractorCost;

    /**
     * @var float
     */
    protected float $_spareCost;

    /**
     * @var Setting
     */
    protected Setting $_status;

    /**
     * @var Shifts
     */
    protected Shifts $_shifts;

    /**
     * @var float
     */
    private float $totalCost;

    /**
     * @var float
     */
    private float $totalProfit;


    /**
     * Database columns when updating/adding. Don't need to include ID column in this array.
     *
     * @var array
     */
    protected array $_columns = [
        'date_started' => [
            'type' => 'date',
            'required' => true,
            'property' => 'dateStarted'
        ],
        'date_finished' => [
            'type' => 'date'
        ],
        'status' => [
            'type' => 'string',
        ],
        'priority' => [
            'type' => 'id',
            'required' => true,
        ],
        'customer' => [
            'type' => 'id',
        ],
        'furniture' => [
            'type' => 'string',
            'required' => true
        ],
        'description' => [
            'type' => 'string',
        ],
        'sale_price' => [
            'type' => 'float',
        ],
        'material_cost' => [
            'type' => 'float',
        ],
        'contractor_cost' => [
            'type' => 'float',
        ],
        'spare_cost' => [
            'type' => 'float',
        ]
    ];

    /**
     * Flag if property has changed when set to be checked when updating database.
     * Special behaviour for Job Furniture
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if ( $name !== 'furniture' ) {
            parent::__set( $name, $value );
            return;
        }
        foreach ( $this->furniture as $furniture ) {
            $oldValue[$furniture->id] = $furniture->quantity;
        }
        parent::__set( $name, $value );
        foreach ( $this->furniture as $furniture ) {
            $newValue[$furniture->id] = $furniture->quantity;
        }
        $this->changed['furniture'] = ($oldValue ?? []) !== ($newValue ?? []);

    }


    /**
     * @param Shift[] $shifts
     * @return Shifts
     */
    protected function shifts(array $shifts = []): Shifts
    {
        if ( empty( $shifts ) ) {
            return $this->_shifts ?? new Shifts();
        }
        return $this->_shifts = new Shifts( $shifts );
    }

    /**
     * Get latest shift by a certain user, or leave $userID blank to get latest shift by anyone
     *
     * @param null $userID
     * @return Shift|null
     */
    public function getLastShift($userID = null): ?Shift
    {
        $this->shifts->orderLatestToEarliest();
        foreach ( $this->shifts->getAll() as $shift ) {
            $workerID = $shift->worker->id ?? $shift->worker ?? null;
            if ( $workerID === $userID ) {
                return $shift;
            }
        }
        return null;
    }

    /**
     * @param int|Customer|null $customer
     * @return Customer
     */
    protected function customer($customer = null)
    {
        if ( $customer !== null ) {
            if ( is_int( $customer ) ) {
                $customerID = $customer;
                $customer = new Customer();
                $customer->id = $customerID;
            }
            $this->_customer = $customer;
        }
        return $this->_customer ?? new Customer();
    }

    /**
     * @param string $dateStarted
     * @return string
     */
    protected function dateStarted(string $dateStarted = ''): string
    {
        if ( !empty( $dateStarted ) ) {
            $this->_dateStarted = $dateStarted;
        }
        return $this->_dateStarted ?? '';
    }

    /**
     * @param string $dateFinished
     * @return string
     */
    protected function dateFinished(string $dateFinished = ''): string
    {
        if ( !empty( $dateFinished ) ) {
            $this->_dateFinished = $dateFinished;
        }
        return $this->_dateFinished ?? '';
    }

    /**
     * @param string $description
     * @return string
     */
    protected function description(string $description = ''): string
    {
        if ( !empty( $description ) ) {
            $this->_description = $description;
        }
        if ( $this->id === 0 ) {
            return 'N/A';
        }
        return $this->_description ?? '';
    }

    /**
     * Converts database JSON string into array and stores array for later conversion to Furniture Class instances. Or just stores Furniture Class instances.
     *
     * @param string|Furniture[]|null $input
     * @return Furniture[]
     */
    protected function furniture($input = null): array
    {
        if ( $input === null ) {
            return $this->_furniture ?? [];
        }

        if ( is_array( $input ) ) {//Array of Furniture instances

            return $this->_furniture = $input ?? [];

        }

        //JSON string


        $furnitureArray = json_decode( $input, true );
        if ( empty( $furnitureArray ) ) {
            return [];
            //$job->furniture = [];
            //continue;
        }
        foreach ( $furnitureArray as $item ) {

            $furnitureID = key( $item );
            //$furnitureQuantity = array_shift( $item );

            //$furnitureInstance = $furnitureFactory->getNew();
            $furnitureInstance = new Furniture();
            $furnitureInstance->id = $furnitureID;
            $furnitureInstance->quantity = array_shift( $item );

            $jobFurniture[$furnitureID] = $furnitureInstance;
        }

        return $this->_furniture = $jobFurniture ?? [];
    }

    /**
     * @param bool $includeLink
     * @return string
     */
    public function getFurnitureString(bool $includeLink = true): string
    {
        $furnitureString = [];

        $jobFurniture = $this->furniture;

        if ( empty( $jobFurniture ) ) {
            return 'None';
        }
        if ( !is_array( $jobFurniture ) ) {
            return '';
        }
        foreach ( $jobFurniture as $furniture ) {
            $furnitureString[] = $furniture->getFurnitureString( $includeLink );
        }
        return implode( '<br>', $furnitureString );
    }

    /**
     * @return float
     */
    public function getTotalCost(): float
    {
        if ( !empty( $this->totalCost ) ) {
            return $this->totalCost;
        }
        return $this->totalCost = $this->shifts->getTotalWorkerCost() + $this->materialCost + $this->contractorCost + $this->spareCost;
    }

    /**
     * @return float
     */
    public function getTotalProfit(): float
    {
        if ( !empty( $this->totalProfit ) ) {
            return $this->totalProfit;
        }
        return $this->totalProfit = $this->salePrice - $this->shifts->getTotalWorkerCost() - $this->materialCost - $this->contractorCost - $this->spareCost;
    }

    /**
     * Returns markup = profit/cost
     *
     * @return float
     */
    public function getMarkup(): ?float
    {
        $totalCost = $this->getTotalCost();
        if ( $totalCost > 0 ) {
            return $this->getTotalProfit() / $this->getTotalCost();
        }
        return null;
    }

    /**
     * @param int $priority
     * @return int
     */
    protected function priority(int $priority = 0): int
    {
        if ( !empty( $priority ) ) {
            $this->_priority = $priority;
        }
        return $this->_priority ?? 0;
    }

    /***
     * @param float|null $salePrice
     * @return float
     */
    public function salePrice(float $salePrice = null): ?float
    {
        if ( $salePrice !== null ) {
            return $this->_salePrice = $salePrice;
        }
        return $this->_salePrice ?? null;
    }


    /***
     * @param float|null $materialCost
     * @return float
     */
    public function materialCost(float $materialCost = null): ?float
    {
        if ( $materialCost !== null ) {
            return $this->_materialCost = $materialCost;
        }
        return $this->_materialCost ?? null;
    }

    /***
     * @param float|null $contractorCost
     * @return float
     */
    public function contractorCost(float $contractorCost = null): ?float
    {
        if ( $contractorCost !== null ) {
            return $this->_contractorCost = $contractorCost;
        }
        return $this->_contractorCost ?? null;
    }

    /**
     * @param float|null $spareCost
     * @return float|null
     */
    public function spareCost(float $spareCost = null): ?float
    {
        if ( $spareCost !== null ) {
            return $this->_spareCost = $spareCost;
        }
        return $this->_spareCost ?? null;
    }

    /**
     * @param int|string|Setting|null $status
     * @return Setting
     */
    protected function status($status = null): Setting
    {
        if ( $status !== null ) {
            if ( is_int( $status ) ) {
                $statusID = $status;
                $status = new Setting();
                $status->id = $statusID;
            }

            if ( is_string( $status ) ) {
                $statusName = $status;
                $status = new Setting();
                $status->name = $statusName;
            }

            $this->_status = $status;
        }
        return $this->_status ?? new Setting();
    }

    /**
     * @return array
     */
    public function completeCheck(): array
    {
        /*
        $errors = $this->healthCheck();
        if ( !empty( $errors ) ) {
            return $errors;
        }
        */
        if ( empty( $this->salePrice ) ) {
            $errors[] = 'Job has no sale price set.';
        }
        if ( $this->dateFinished === '' ) {
            $errors[] = 'Job has no finish date set.';
        }

        if ( $this->status->name !== 'jobstat_yellow' && $this->status->name !== 'jobstat_green' ) {
            $errors[] = 'Job status is incomplete.';
        }
        foreach ( $errors ?? [] as $error ) {
            $return[] = [
                'content' => $error,
                'class' => 'warning'
            ];
        }
        return $return ?? [];
    }

    /**
     * @return array
     */
    public function doHealthCheck(): array
    {
        if ( $this->id === 0 ) {
            return [];
        }
        if ( $this->customer->id === null ) {
            $errors[] = 'Job has no <strong>customer</strong> assigned.';
        }
        if ( empty( $this->furniture ) ) {
            $errors[] = 'No <strong>furniture</strong> assigned.';
        } elseif ( is_iterable( $this->_furniture ) ) {
            foreach ( $this->_furniture as $furniture ) {
                if ( empty( $furniture->name ) ) {
                    $errors[] = 'Job has assigned <strong>furniture</strong>' . $furniture->getIDBadge( null, 'danger' ) . " that doesn't exist in the database.";
                }
            }
        }
        $dateStarted = $this->dateStarted;
        $dateFinished = $this->dateFinished;
        if ( empty( $dateStarted ) ) {
            $errors[] = 'No <strong>start date</strong> set.';
        }
        if ( !empty( $dateStarted ) && DateTimeUtility::isBefore( $dateFinished, $dateStarted, false ) ) {
            $errors[] = 'Job has <strong>finish date</strong> earlier than <strong>start date</strong>.';
        }
        if ( !empty( $dateFinished ) && $this->status->name === 'jobstat_red' ) {
            $errors[] = 'Job <strong>finish date</strong> has been set but <strong>status</strong> is still "in progress".';
        }
        return $errors ?? [];
    }

    /**
     * @return string
     */
    public function getAssociatedEntities(): string
    {
        $numberOfShifts = $this->shifts->getCount();
        if ( $numberOfShifts > 0 ) {
            return ' This job has <strong>' . $numberOfShifts . '</strong> shifts associated with it. These will also be deleted.';
        }
        return '';
    }

    /**
     * @return string
     */
    public function getArchiveLink(): string
    {
        return parent::getArchiveLink() . '&order_by=date_started';
    }

    /**
     * @return array
     */
    public function getCustomNavItems(): array
    {
        $archivePage = $this->getArchiveLink();
        return [
            'in_progress' => [
                'href' => $archivePage . '&query[status]=jobstat_red',
                'content' => 'In Progress Jobs'
            ],
            'urgent' => [
                'href' => $archivePage . '&query[status]=jobstat_red&query[priority]=1',
                'content' => 'Urgent Jobs'
            ],
            'errors' => [
                'href' => $archivePage . '&query[status]=jobstat_red&query[priority]=1',
                'content' => 'Jobs With Errors'
            ],
            'all' => [
                'href' => $archivePage,
            ]
        ];
    }


    /**
     * Get DB input array
     *
     * @return array
     * @throws \JsonException
     */
    protected function getSaveData(): array
    {
        $data = parent::getSaveData();
        if ( !empty( $data['furniture'] ) ) {
            foreach ( $data['furniture'] as $furniture ) {
                $dataFurniture[][$furniture->id] = $furniture->quantity;
            }
            $data['furniture'] = json_encode( $dataFurniture ?? null, JSON_THROW_ON_ERROR );
        }
        if ( !empty( $data['status'] ) ) { //We want string for DB column, not id integer
            $data['status'] = $this->status->name;
        }
        return $data;
    }
}
