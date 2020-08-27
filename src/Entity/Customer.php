<?php

namespace Phoenix\Entity;

/**
 * @property string $emailAddress
 * @property string $name
 * @property Job[]  $jobs
 *
 * Class Customer
 *
 * @package Phoenix
 */
class Customer extends Entity
{
    /**
     * @var string Fontawesome icon
     */
    protected string $icon = 'user-tie';

    /**
     * @var string
     */
    protected string $_emailAddress;

    /**
     * @var string
     */
    protected string $_name;

    /**
     * @var Job[]
     */
    protected array $_jobs;

    /**
     * Map of DB table columns.
     * Arrays keys are column names.
     * 'property' is matching Class property.
     * 'type' is data type for validation,
     * 'required' flags that data must be present to be added as DB row
     *
     * Don't include ID column in this array as it's added in constructor.
     *
     * @var array
     */
    protected array $_columns = [
        'name' => [
            'type' => 'name',
            'required' => true
        ],
        'email_address' => [
            'type' => 'string',
            'property' => 'emailAddress'
        ]
    ];

    /**
     * @param string $name
     * @return string
     */
    protected function name(string $name = ''): string
    {
        if ( !empty( $name ) ) {
            $this->_name = trim( $name );
        }
        return $this->_name ?? '';
    }

    /**
     * @return string
     */
    public function getNamePossessive(): string
    {
        $name = $this->name;
        if ( empty( $name ) ) {
            return '';
        }
        if ( substr( $name, -1 ) === 's' ) {
            return $name . "'";
        }
        return $name . "'s";
    }

    /**
     * @param string $emailAddress
     * @return string
     */
    protected function emailAddress(string $emailAddress = ''): string
    {
        if ( !empty( $emailAddress ) ) {
            $this->_emailAddress = $emailAddress;
        }
        return $this->_emailAddress ?? '';
    }

    /**
     * @param Job[] $jobs
     * @return Job[]
     */
    protected function jobs(array $jobs = []): array
    {
        if ( !empty( $jobs ) ) {
            $this->_jobs = $jobs;
        }
        return $this->_jobs ?? [];
    }

    /**
     * @return bool
     */
    public function canDelete(): bool
    {
        $jobs = $this->jobs;
        $numberOfJobs = count( $jobs );


        if ( $numberOfJobs > 0 ) {
            $nameType = $numberOfJobs > 1 ? 'entityNamePlural' : 'entityName';
            $name = array_shift( $jobs )->$nameType;
            return $this->addError( 'This customer has <strong>' . $numberOfJobs . '</strong> ' . $name . ' associated with it. A customer cannot be deleted until its related jobs are deleted.' );
        }
        return true;
    }

    /**
     * @param array $errors
     * @return string
     */
    public function healthCheck(array $errors = []): string
    {
        if ( empty( $this->name ) ) {
            $errors[] = 'Customer should have a name.';
        }
        return parent::healthCheck( $errors );
    }
}