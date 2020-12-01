<?php

namespace Phoenix\Entity;

/**
 * @method Setting[] getEntities(array $queryArgs = [], $provision = false)
 *
 * Class SettingFactory
 */
class SettingFactory extends EntityFactory
{
    /**
     * @var string
     */
    protected string $entityName = 'setting';

    /**
     * @return Setting
     */
    protected function instantiateEntityClass(): Setting
    {
        return new Setting( $this->db, $this->messages );
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getSetting(string $name = ''): string
    {
        $settings = $this->getEntities( ['name' => $name] );
        if ( !empty( $settings ) ) {
            return array_shift( $settings )->value;
        }
        return '';
    }

    /**
     * @return string
     */
    public function getCutoffTime(): string
    {
        $cutoffTime = $this->getSetting( 'cutoff_time' );
        if ( !empty( $cutoffTime ) ) {
            return date( 'H:i', strtotime( $cutoffTime ) );
        }
        return '';
    }

    /**
     * @return array
     */
    public function getJobStatusesOptionsArray(): array
    {
        return array_column(
            $this->getEntities( ['name' => [
                'value' => 'jobstat',
                'operator' => 'LIKE']
            ] ),
            'value', 'name'
        );
    }
}