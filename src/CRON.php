<?php

namespace Phoenix;

/**
 * Class CRON
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class CRON
{
    /**
     * @var array
     */
    private array $actions = [];

    /**
     * @var array|string[]
     */
    private array $actionTitles = [
        'auto_clockoff' => 'Auto Clockoff',
        'backup_db' => 'Backup Database'
    ];

    /**
     * @var Init|null
     */
    private ?Init $init;

    /**
     *
     */
    public function __construct(Init $init, $args = [])
    {
        $this->init = $init;

        array_shift( $args );
        foreach ( $args as $arg ) {
            $actionTitle = $this->actionTitles[$arg] ?? '';
            if ( !empty( $actionTitle ) ) {
                $this->actions[$arg] = $actionTitle;
            }
        }
        $subject = implode( ' and ', $this->actions );

        $config = $init->getConfig();
        $append = 'CRM';
        $subject = trim( str_replace( $append, '', $config['system_title'] ) ) . ' ' . $append . (!empty( $subject ) ? ' ' . $subject : '');
        $init->getMessages()
            ->doingCRON()
            ->setEmailArgs( [
                'prepend' => $subject . ' - ',
                'subject' => $subject,
                'to' => $config['email']['to'],
                'from' => $config['email']['from'],
                'from_name' => $config['system_title']
            ] )
            ->add( 'Starting.' );
    }

    /**
     * @return void
     */
    public function doActions()
    {
        $init = $this->init;
        $messages = $init->getMessages();
        if ( empty( $this->actions ) ) {
            $messages->add( 'No action or erroneous action requested. Add a legitimate parameter to the command line call.' );
        }
        foreach ( $this->actions as $arg => $actionTitle ) {
            $messages->add( 'Starting ' . $actionTitle . '.' );
            switch( $arg ) {
                case 'auto_clockoff':
                    $init->getDirector( 'crm' )->doActions( ['finish_shifts' => true] );
                    break;
                case 'backup_db':
                    $init->getDB()->backup();
                    break;
            }
            $messages->add( 'Finished ' . $actionTitle . '.' );
        }

        $messages->add( 'Finished.' );
        if ( $messages->email() ) {
            $messages->add( 'Emailed Results.' );
        }
        return;
    }
}