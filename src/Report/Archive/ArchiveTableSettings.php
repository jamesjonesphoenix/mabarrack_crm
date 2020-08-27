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
        ],
        'value' => [
            'title' => 'Value',
        ]
    ];

    /**
     * @param Setting $setting
     * @return array
     */
    public function extractEntityData($setting): array
    {
        return [
            'name' => $setting->name ?? '&minus;',
            'value' => $setting->value ?? '&minus;',
        ];
    }
}