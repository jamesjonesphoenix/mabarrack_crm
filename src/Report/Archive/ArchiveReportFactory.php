<?php


namespace Phoenix\Report\Archive;


use Phoenix\Report\ReportFactoryBase;

/**
 * Class ArchiveReportFactory
 *
 * @author James Jones
 * @package Phoenix\Report\Archive
 *
 */
class ArchiveReportFactory extends ReportFactoryBase
{
    /**
     * @param string $entity
     * @return ArchiveTable
     */
    public function get(string $entity = ''): ArchiveTable
    {
        $library = [
            'customer' => 'Customers',
            'furniture' => 'Furniture',
            'job' => 'Jobs',
            'setting' => 'Settings',
            'shift' => 'Shifts',
            'user' => 'Users',
        ];
        if ( empty( $library[$entity] ) ) {
            trigger_error( "Requesting ArchiveTable class that doesn't exist - " . $entity . '.' );
        }
        $classname = 'Phoenix\Report\Archive\ArchiveTable' . $library[$entity];
        //$classname = $library[$entity];
        return new $classname(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
    }

    /**
     * @return ArchiveTableShifts
     */
    public function getShifts(): ArchiveTableShifts
    {
        return new ArchiveTableShifts(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
    }

    /**
     * @return ArchiveTableShiftsWorkerHome
     */
    public function getShiftsWorkerHome(): ArchiveTableShiftsWorkerHome
    {
        return new ArchiveTableShiftsWorkerHome(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
    }

    /**
     * @return ArchiveTableJobsProfitLoss
     */
    public function getJobsProfitLoss(): ArchiveTableJobsProfitLoss
    {
        return new ArchiveTableJobsProfitLoss(
            $this->htmlUtility,
            $this->format,
            $this->url
        );
    }
}