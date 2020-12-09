<?php

namespace Phoenix\Entity;

use Phoenix\URL;

/**
 * @property string $emailAddress
 * @property string $name
 * @property string $phoneNumber
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
     * @var string
     */
    protected string $_phoneNumber;

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
        ],
        'phone_number' => [
            'type' => 'string',
            // 'property' => 'emailAddress'
        ]
    ];


    /**
     * @param string|null $name
     * @return string
     */
    protected function name(string $name = null): string
    {
        if (  $name !== null ) {
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
     * @return string
     */
    public function getProfitLossLink(): string
    {
        return (new URL())
            ->setQueryArgs( [
                'page' => 'report',
                'report' => 'profit_loss',
                'customer' => $this->id,
                'date_start' => '1900-01-01',
                'date_finish' => '2200-01-01'
            ] )
            ->write();
    }

    /**
     * @param string|null $emailAddress
     * @return string
     */
    protected function emailAddress(string $emailAddress = null): string
    {
        if ( $emailAddress !== null ) {
            $this->_emailAddress = $emailAddress;
        }
        return $this->_emailAddress ?? '';
    }

    /**
     * @param string|null $phoneNumber
     * @return string
     */
    protected function phoneNumber(string $phoneNumber = null): string
    {
        if (  $phoneNumber !== null ) {
            $this->_phoneNumber = $phoneNumber;
        }
        return $this->_phoneNumber ?? '';
    }

    /**
     * @param bool $anchorLink
     * @return string
     */
    public function getEmailLink(bool $anchorLink = false): string
    {
        if ( empty( $this->emailAddress ) ) {
            return '';
        }
        $href = 'mailto:' . $this->emailAddress;
        if ( $anchorLink ) {
            return '<a href="' . $href . '" class="text-white">' . $this->emailAddress . '</a>';
        }
        return $href;
    }

    /**
     * @param bool $anchorLink
     * @return string
     */
    public function getPhoneLink(bool $anchorLink = false): string
    {
        if ( empty( $this->phoneNumber ) ) {
            return '';
        }
        $href = 'tel:' . $this->phoneNumber;
        if ( $anchorLink ) {
            return '<a href="' . $href . '" class="text-white">' . $this->phoneNumber . '</a>';
        }
        return $href;
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
     * @return array
     */
    public function doHealthCheck(): array
    {
        if ( empty( $this->name ) ) {
            $errors[] = 'Customer should have a name.';
        }
        return $errors ?? [];
    }
}