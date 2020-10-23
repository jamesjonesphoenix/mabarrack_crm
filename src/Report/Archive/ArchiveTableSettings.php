<?php


namespace Phoenix\Report\Archive;


use Phoenix\Entity\Setting;

/**
 * Class ArchiveTableSettings
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableSettings extends ArchiveTable
{
    /**
     * @var array
     */
    protected array $columns = [
        'name' => [
            'title' => 'Name',
            'default' => '&minus;'
        ],
        'description' => [
            'title' => 'Description',
            'default' => '&minus;'
        ],
        'value' => [
            'title' => 'Value',
            'default' => '&minus;'
        ]
    ];

    /**
     * @param Setting $setting
     * @return array
     */
    public function extractEntityData($setting): array
    {
        return [
            'name' => $setting->name,
            'description' => $setting->description,
            'value' => $setting->value,
        ];
    }
}