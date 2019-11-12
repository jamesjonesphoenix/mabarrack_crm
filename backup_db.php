<?php

namespace Phoenix;

define( 'DOING_CRON', true );
include __DIR__ . '/src/crm_init.php';
if ( !defined( 'SYSTEM_TITLE' ) ) { //check if crm_init included
    trigger_error( 'Was unable to include crm_init.php', E_USER_ERROR );
    exit();
}
$messages = Messages::instance();
$messages->emailArgs( array(
    'prepend' => SYSTEM_TITLE . ' CRM - CRON backup database - ',
    'subject' => SYSTEM_TITLE . ' CRM - CRON backup database',
    'to' => TO_EMAIL,
    'from' => FROM_EMAIL
) );

$messages->add( 'Starting.' );
$filename = __DIR__ . '/backups/' . date( 'Y-m-d-H_i_s' ) . '-' . DB_NAME . '-database_backup.sql.gz';

$returnVar = NULL;
$output = NULL;
exec( '(mysqldump --single-transaction -u ' . DB_USER . ' -p' . DB_PASSWORD . ' -h ' . DB_HOST . ' -P ' . DB_PORT . ' ' . DB_NAME . ' | gzip > ' . $filename . ') 2>&1', $output, $returnVar );
if ( !empty( $returnVar ) ) {
    $messages->add( 'Error when attempting mysqldump - ' . $returnVar . '. See output - ' . implode( ',', $output ) );
} else {
    $messages->add( 'Database backed up to ' . $filename . '.' );
}
$messages->add( 'Finished' );
$messages->email();
