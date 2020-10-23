<?php

namespace Phoenix;

require_once __DIR__ . '/vendor/autoload.php';

$init = (new Init())
    ->doingCRON()
    ->startUp();
$messages = $init->getMessages();
$messages->add( 'Starting.' );
switch( $argv[1] ?? '' ) {
    case 'auto_clockoff':
        $subject = 'Auto Clockoff';
        $init->getDirector( 'crm' )->doActions( ['finish_shifts' => true] );
        break;
    case 'backup_db':
        $subject = 'Backup Database';
        $init->getDB()->backup();
        break;
    default:
        $messages->add( 'No action requested. Add a legitimate parameter to the command line call.' );
}
if ( !empty( $subject ) ) {
    $subject = trim( str_replace( 'CRM', '', $init->getConfig()['system_title'] ) ) . ' CRM - Scheduled ' . $subject;
    $messages->setEmailArgs( [
        'subject' => $subject,
        'prepend' => $subject . ' - '
    ] );
}
$messages->add( 'Finished' );
$messages->email();