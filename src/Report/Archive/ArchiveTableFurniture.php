<?php


namespace Phoenix\Report\Archive;


use Phoenix\Entity\Furniture;

/**
 * Class ArchiveTableFurniture
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveTableFurniture extends ArchiveTable
{
    /**
     * @var array
     */
    protected array $columns = [
        'name' => [
            'title' => 'Name',
            'default' => '&minus;',
        ],
        'plural_name' => [
            'title' => 'Plural Name',
            'default' => '&minus;',
        ]
    ];

    /**
     * @param Furniture $furniture
     * @return array
     */
    public function extractEntityData($furniture): array
    {
        return [
            'name' => $furniture->name,
            'plural_name' => $furniture->namePlural,
        ];
    }
}