<?php

namespace Phoenix;

use Phoenix\Page\ArchivePage\ArchivePageBuilderCustomer;
use Phoenix\Page\ArchivePage\ArchivePageBuilderFurniture;
use Phoenix\Page\ArchivePage\ArchivePageBuilderJob;
use Phoenix\Page\ArchivePage\ArchivePageBuilderSettings;
use Phoenix\Page\ArchivePage\ArchivePageBuilderShift;
use Phoenix\Page\ArchivePage\ArchivePageBuilderUser;
use Phoenix\Page\ReportPage\ReportPageBuilderActivitySummary;
use Phoenix\Page\ReportPage\ReportPageBuilderProfitLoss;
use Phoenix\Page\DetailPage\DetailPageBuilderCustomer;
use Phoenix\Page\DetailPage\DetailPageBuilderFurniture;
use Phoenix\Page\DetailPage\DetailPageBuilderJob;
use Phoenix\Page\DetailPage\DetailPageBuilderSetting;
use Phoenix\Page\DetailPage\DetailPageBuilderShift;
use Phoenix\Page\DetailPage\DetailPageBuilderUser;
use Phoenix\Page\IndexPageBuilder;

require_once __DIR__ . '/../vendor/autoload.php';


$init = (new Init())->startUp();
$db = $init->getDB();
$messages = $init->getMessages();
$entityType = $_GET['entity'] ?? '';

switch( $_GET['page'] ?? '' ) {
    case 'archive':
        switch( $entityType ) {
            case 'customer':
            case 'customers':
                $pageBuilder = new ArchivePageBuilderCustomer( $db, $messages );
                break;
            case 'furniture':
                $pageBuilder = new ArchivePageBuilderFurniture( $db, $messages );
                break;
            case 'job':
            case 'jobs':
                $pageBuilder = new ArchivePageBuilderJob( $db, $messages );
                break;
            case 'shift':
            case 'shifts':
                $pageBuilder = new ArchivePageBuilderShift( $db, $messages );
                break;
            case 'user':
            case 'users':
                $pageBuilder = new ArchivePageBuilderUser( $db, $messages );
                break;
            case 'setting':
            case 'settings':
                $pageBuilder = new ArchivePageBuilderSettings( $db, $messages );
                break;
            default:
                if ( empty( $entityType ) ) {
                    $messages->add( 'Redirected to main page because no entity type requested for archive page.' );
                } else {
                    $messages->add( 'Redirected to main page because archive exists for <strong>' . $entityType . '</strong> entity type.' );
                }
                redirect( 'index.php' );
                exit;
            //$pageBuilder = new 404PageBuilderSettings( $db, $messages );
        }
        $pageBuilder->setInputArgs( $_GET ?? [] );
        break;
    case 'detail':
        switch( $entityType ) {
            case 'customer':
            case 'customers':
                $pageBuilder = new DetailPageBuilderCustomer( $db, $messages );
                break;
            case 'furniture':
                $pageBuilder = new DetailPageBuilderFurniture( $db, $messages );
                break;
            case 'job':
            case 'jobs':
                $pageBuilder = new DetailPageBuilderJob( $db, $messages );
                break;
            case 'shift':
            case 'shifts':
                $pageBuilder = new DetailPageBuilderShift( $db, $messages );
                break;
            case 'user':
            case 'users':
                $pageBuilder = (new DetailPageBuilderUser( $db, $messages ))->setStartDate( $_GET['start_date'] ?? '' );
                break;
            case 'setting':
            case 'settings':
                $pageBuilder = new DetailPageBuilderSetting( $db, $messages );
                break;
            default:
                if ( empty( $entityType ) ) {
                    $messages->add( 'Redirected to main page because no entity type requested for detail page.' );
                } else {
                    $messages->add( 'Redirected to main page because no detail page exists for <strong>' . $entityType . '</strong> entity type.' );
                }
                redirect( 'index.php' );
                exit;
        }
        $pageBuilder->setInputArgs( $_GET ?? [] );
        break;
    case 'report':
        $report = $_GET['report'] ?? '';
        switch( $report ) {
            case 'profit_loss':
                $pageBuilder = new ReportPageBuilderProfitLoss( $db, $messages );
                break;
            case 'activity_summary':
                $pageBuilder = new ReportPageBuilderActivitySummary( $db, $messages );
                break;
            default:
                if ( empty( $report ) ) {
                    $messages->add( 'Redirected to main page because no report type was requested for report page.' );
                } else {
                    $messages->add( 'Redirected to main page because <strong>' . $report . "</strong> report type doesn't exist." );
                }
                redirect( 'index.php' );
                exit;
        }
        $pageBuilder
            ->setDates( $_GET['date_start'] ?? '',$_GET['date_finish'] ?? '' )
            ->setReportType( $report );

        break;
    default:
        $pageBuilder = new IndexPageBuilder( $db, $messages );
        break;
}

$pageBuilder->buildPage()->getPage()->render();