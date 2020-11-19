<?php


namespace Phoenix;


use Phoenix\Entity\ShiftFactory;
use Phoenix\Page\ArchivePage\ArchivePageBuilder;
use Phoenix\Page\DetailPage\DetailPageBuilder;
use Phoenix\Page\IndexPageBuilder;
use Phoenix\Page\PageBuilder;
use Phoenix\Page\ReportPage\ReportPageBuilder;

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

                if ( $pageType === 'detail' ) {
                    $pageBuilder = DetailPageBuilder::create( $this->db, $this->messages, $this->url, $entityType );
                } else {
                    $pageBuilder = ArchivePageBuilder::create( $this->db, $this->messages, $this->url, $entityType );
                }
                //$pageBuilder = EntityPageBuilder::create($this->db, $this->messages, $this->url, $pageType, $entityType );

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
                $pageBuilder = ReportPageBuilder::create( $this->db, $this->messages, $this->url, $reportType );
                if ( $pageBuilder !== null ) {
                    return $pageBuilder->setInputArgs($inputArray);
                }
                if ( empty( $reportType ) ) {
                    $message = 'Redirected to main page because no report type was requested for report page.';
                } else {
                    $message = 'Redirected to main page because <strong>' . $reportType . "</strong> report type doesn't exist.";
                }
        }

        if ( empty( $message ) ) {
            return new IndexPageBuilder( $this->db, $this->messages, $this->url );
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