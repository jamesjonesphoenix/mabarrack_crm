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
        $init->getDirector( 'crm' )->doActions( ['finish_shifts' => true] );
        break;
    case 'backup_db':
        $init->getDB()->backup();
        break;
    default:
        $messages->add( 'No action requested. Add a legitimate parameter to the command line call.' );
}
$messages->add( 'Finished' );
$messages->email();