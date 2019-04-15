<?php
define( 'DOING_CRON', true );
$message_prepend = 'Mabarrack CRM - CRON backup database - ';
if ( ( include 'public_html/include/crm_init.php' ) != TRUE ) {
    $fail_message = $message_prepend . 'Was unable to include crm_init.php';
    trigger_error( $fail_message );
    echo $fail_message;
    exit();
}

$ph_cron_log = new ph_Cron_Logging( $message_prepend, array(
    'subject' => 'Mabarrack CRM CRON backup database',
    'to' => TO_EMAIL,
    'from' => FROM_EMAIL
) );
$ph_cron_log->add_log( 'Starting.' );

$filename = '~/crm/backups/' . date( 'Y-m-d-H_i_s' ) . '-' . DB_NAME . '-database_backup.sql.gz';
$return_var = NULL;
$output = NULL;
exec( '(mysqldump --single-transaction -u ' . DB_USER . ' -p' . DB_PASSWORD . ' -h ' . DB_HOST . ' -P ' . DB_PORT . ' ' . DB_NAME . ' | gzip > ' . $filename . ') 2>&1', $output, $return_var );

if ( !empty( $return_var ) )
    $ph_cron_log->add_log( 'Error when attempting mysqldump - ' . $return_var . '. See output - ' . var_dump( $output ) );
else
    $ph_cron_log->add_log( 'Finished. Database backed up to ' . $filename . '.' );
$ph_cron_log->email_log();