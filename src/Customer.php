<?php

namespace Phoenix;

/**
 *
 * @property string $name
 * @property Job[] $jobs
 *
 * Class Customer
 *
 * @package Phoenix
 */
class Customer extends Entity
{
    /**
     * @var string
     */
    protected $_name;

    /**
     * @var Job[]
     */
    protected $_jobs;

    /**
     * @var string
     */
    protected $_tableName = 'customers';

    /**
     * @param string $name
     * @return string
     */
    protected function name(string $name = ''): string
    {
        if ( !empty( $name ) ) {
            $this->_name = $name;
        }
        return $this->_name ?? 'Unknown';
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
}