<?php


namespace Phoenix;


use Phoenix\Entity\ShiftFactory;
use Phoenix\Page\ArchivePage\ArchivePageBuilder;
use Phoenix\Page\ArchivePage\ArchivePageBuilderCustomer;
use Phoenix\Page\ArchivePage\ArchivePageBuilderFurniture;
use Phoenix\Page\ArchivePage\ArchivePageBuilderJob;
use Phoenix\Page\ArchivePage\ArchivePageBuilderSettings;
use Phoenix\Page\ArchivePage\ArchivePageBuilderShift;
use Phoenix\Page\ArchivePage\ArchivePageBuilderUser;
use Phoenix\Page\DetailPage\DetailPageBuilder;
use Phoenix\Page\DetailPage\DetailPageBuilderCustomer;
use Phoenix\Page\DetailPage\DetailPageBuilderFurniture;
use Phoenix\Page\DetailPage\DetailPageBuilderJob;
use Phoenix\Page\DetailPage\DetailPageBuilderSetting;
use Phoenix\Page\DetailPage\DetailPageBuilderShift;
use Phoenix\Page\DetailPage\DetailPageBuilderUser;
use Phoenix\Page\EntityPageBuilder;
use Phoenix\Page\IndexPageBuilder;
use Phoenix\Page\PageBuilder;
use Phoenix\Page\ReportPage\ReportPageBuilder;
use Phoenix\Page\ReportPage\ReportPageBuilderActivitySummary;
use Phoenix\Page\ReportPage\ReportPageBuilderProfitLoss;

/**
 * Class DirectorCRM
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class DirectorCRM extends Director
{
    /*
    public array $dictionary = [
        'customer' => 'ArchivePageBuilderCustomer',
        'furniture' => 'ArchivePageBuilderFurniture',
        // 'job' => new ArchivePageBuilderJob()
    ];
     $className = $this->dictionary[$entityType];
     $pageBuilder = new $className($this->db, $this->messages );
    */

    /**
     * @param string $pageType
     * @param string $entityType
     * @return EntityPageBuilder|null
     */
    public function getEntityPageBuilder(string $pageType = '', string $entityType = ''): ?EntityPageBuilder
    {
        if ( $pageType === 'detail' ) {
            return $this->getDetailPageBuilder( $entityType );
        }
        return $this->getArchivePageBuilder( $entityType );
    }

    /**
     * @param string $entityType
     * @return DetailPageBuilder|null
     */
    public function getDetailPageBuilder(string $entityType = ''): ?DetailPageBuilder
    {
        switch( $entityType ) {
            case 'customer':
            case 'customers':
                return new DetailPageBuilderCustomer( $this->db, $this->messages );
            case 'furniture':
                return new DetailPageBuilderFurniture( $this->db, $this->messages );
            case 'job':
            case 'jobs':
                return new DetailPageBuilderJob( $this->db, $this->messages );
            case 'shift':
            case 'shifts':
                return new DetailPageBuilderShift( $this->db, $this->messages );
            case 'user':
            case 'users':
                return new DetailPageBuilderUser( $this->db, $this->messages );
            case 'setting':
            case 'settings':
                return new DetailPageBuilderSetting( $this->db, $this->messages );
        }
        return null;
    }

    /**
     * @param string $entityType
     * @return ArchivePageBuilder|null
     */
    public function getArchivePageBuilder(string $entityType = ''): ?ArchivePageBuilder
    {
        switch( $entityType ) {
            case 'customer':
            case 'customers':
                return new ArchivePageBuilderCustomer( $this->db, $this->messages );
            case 'furniture':
                return new ArchivePageBuilderFurniture( $this->db, $this->messages );
            case 'job':
            case 'jobs':
                return new ArchivePageBuilderJob( $this->db, $this->messages );
            case 'shift':
            case 'shifts':
                return new ArchivePageBuilderShift( $this->db, $this->messages );
            case 'user':
            case 'users':
                return new ArchivePageBuilderUser( $this->db, $this->messages );
            case 'setting':
            case 'settings':
                return new ArchivePageBuilderSettings( $this->db, $this->messages );
        }
        return null;
    }

    /**
     * @param string $reportType
     * @return ReportPageBuilder|null
     */
    public function getReportPageBuilder(string $reportType = ''): ?ReportPageBuilder
    {
        switch( $reportType ) {
            case 'profit_loss':
                return new ReportPageBuilderProfitLoss( $this->db, $this->messages );
            case 'activity_summary':
            case 'billable_vs_non':
                return new ReportPageBuilderActivitySummary( $this->db, $this->messages );
        }
        return null;
    }

    /**
     * @param array $inputArray
     * @return PageBuilder
     */
    public function getPageBuilder(array $inputArray = []): PageBuilder
    {
        $pageType = $inputArray['page'] ?? '';
        switch( $pageType ) {
            case 'archive':
            case 'detail':
                $entityType = $inputArray['entity'] ?? '';
                $pageBuilder = $this->getEntityPageBuilder( $pageType, $entityType );
                if ( $pageBuilder !== null ) {
                    return $pageBuilder->setInputArgs( $inputArray );
                }
                if ( empty( $entityType ) ) {
                    $message = 'Redirected to main page because no entity type requested for ' . $pageType . ' page.';
                } else {
                    $message = 'Redirected to main page because ' . $pageType . ' page does not exist for <strong>' . $entityType . '</strong> entity type.';
                }
                break;
            case 'report':
                $reportType = $inputArray['report'] ?? '';
                $pageBuilder = $this->getReportPageBuilder( $reportType );
                if ( $pageBuilder !== null ) {
                    return $pageBuilder
                        ->setDates( $inputArray['date_start'] ?? '', $inputArray['date_finish'] ?? '' )
                        ->setReportType( $reportType );
                }
                if ( empty( $reportType ) ) {
                    $message = 'Redirected to main page because no report type was requested for report page.';
                } else {
                    $message = 'Redirected to main page because <strong>' . $reportType . "</strong> report type doesn't exist.";
                }
        }

        if ( empty( $message ) ) {
            return new IndexPageBuilder( $this->db, $this->messages );
        }
        $this->messages->add( $message );
        redirect( 'index.php' );
        exit;
    }

    /**
     * @param array $inputArray
     * @return bool
     * @throws \Exception
     */
    public function doActions(array $inputArray = []): bool
    {
        if ( !empty( $inputArray['finish_shifts'] ) ) {
            $this->finishUnfinishedShifts();
        }
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function finishUnfinishedShifts(): bool
    {
        $unfinishedShifts = (new ShiftFactory( $this->db, $this->messages ))->getAllUnfinishedShift();

        $numberOfShifts = $unfinishedShifts->getCount();
        if ( $numberOfShifts === 0 ) {
            $this->messages->add( 'No unfinished shifts found.' );
            return true;
        }
        //there are unfinished shifts from the day
        $this->messages->add( 'Found ' . $numberOfShifts
            . ' unfinished shift'
            . ($numberOfShifts > 1 ? 's' : '')
            . '.' );

        foreach ( $unfinishedShifts->getAll() as $unfinishedShift ) {
            $unfinishedShift->finishShift();
        }
        return true;
    }
}