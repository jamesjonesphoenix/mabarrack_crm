<?php

class ph_Cron_Logging
{

    public $email_message = '';
    public $message_prepend = '';
    public $email_args = array();

    function __construct( $message_prepend = false, $email_args = false ) {
        return $this->init( $message_prepend, $email_args );
    }

    function init( $message_prepend = false, $email_args = false ) {
        if ( !empty( $message_prepend ) )
            $this->message_prepend = $message_prepend;
        if ( !empty( $email_args ) ) {
            $this->email_args[ 'subject' ] = !empty( $email_args[ 'subject' ] ) ? $email_args[ 'subject' ] : 'CRON log';

            if ( !empty( $email_args[ 'to' ] ) )
                $this->email_args[ 'to' ] = $email_args[ 'to' ];
            elseif ( defined( 'TO_EMAIL' ) )
                $this->email_args[ 'to' ] = TO_EMAIL;

            if ( !empty( $email_args[ 'from' ] ) )
                $this->email_args[ 'from' ] = $email_args[ 'from' ];
            elseif ( defined( 'FROM_EMAIL' ) )
                $this->email_args[ 'from' ] = FROM_EMAIL;
        }
        return true;
    }

    function add_log( $message = false ) {
        if ( !empty( $message ) ) {
            $message = $this->message_prepend . $message;
            trigger_error( $message );
            echo $message . "\r\n";
            $this->email_message .= $message . '<br>';
        }
    }

    function email_log() {
        if ( empty( $this->email_args[ 'from' ] ) || empty( $this->email_args[ 'to' ] ) || empty( $this->email_args[ 'subject' ] ) )
            return false;
        $headers = "From: Mabarrack CRM <" . $this->email_args[ 'from' ] . ">" . "\r\n";
        $headers .= "Mime-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        if ( mail( $this->email_args[ 'to' ], $this->email_args[ 'subject' ], '<h1>Results</h1>' . $this->email_message, $headers ) )
            return true;
        return false;
    }
}