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
        return array_shift( $settings )->value;
    }
}