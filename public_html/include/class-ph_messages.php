<?php

class ph_Messages
{
    protected static $_instance = null;

    public $messages = array();
    public $message_codes = array();
    public $current_user;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct( $ph_user = false ) {
        $this->init( $ph_user );
    }

    function init( $ph_user = false ) {
        $this->current_user = $ph_user;
        if ( !empty( $_SESSION[ 'message' ] ) ) {
            $this->add_stateful_messages( $_SESSION[ 'message' ] );
            unset( $_SESSION[ 'message' ] );
        }
        if ( !empty( $_GET[ 'message' ] ) )
            $this->add_stateful_messages( $_GET[ 'message' ] );
        return true;
    }

    function add_message( $message_string = false, $message_type = 'danger' ) {
        if ( !empty( $message_string ) ) {
            $this->messages[] = array( 'string' => $message_string, 'type' => $message_type );
            return true;
        }
    }

    function is_message() {
        if ( empty( $this->messages ) )
            return false;
        return true;
    }

    function display() {
        if ( count( $this->messages ) == 0 )
            return false;

        $message_html = '<div class="messages">';
        foreach ( $this->messages as $message ) {
            if ( !empty( $message[ 'string' ] ) ) {
                $message_type = !empty( $message[ 'type' ] ) ? $message[ 'type' ] : 'danger';
                $message_html .= sprintf( '<div class="alert alert-' . $message_type . '" role="alert">%s</div>', $message[ 'string' ] );
            }
        }
        $message_html .= '</div>';
        echo $message_html;

    }

    function add_session_messages() {
        if ( empty( $_SESSION[ 'messages' ] ) || !is_array( $_SESSION[ 'messages' ] ) )
            return;
        foreach ( $_SESSION[ 'messages' ] as $message ) {
            $this->formulate_message( $message );
        }

    }

    function add_stateful_messages( $message_input = false ) {
        if ( empty( $message_input ) )
            return false;
        if ( is_string( $message_input ) )
            $messages[] = array( 'code' => $message_input );
        elseif ( !empty( $message_input[ 'code' ] ) )
            $messages[] = $message_input;
        else
            $messages = $message_input;
        foreach ( $messages as $message ) {
            if ( is_string( $message[ 'code' ] ) )
                $this->formulate_message( $message );
        }
    }

    /**
     * @param bool $message
     * @return bool
     */
    function formulate_message( $message = false ) {
        if ( empty( $message ) )
            return false;
        switch ( $message[ 'code' ] ) {
            case 'denied':
                if ( $this->current_user )
                    $message_string = 'You were redirected to the ' . $this->current_user->get_role() . ' homepage because you are not allowed to visit ';
                else
                    $message_string = 'You were redirected to this page because you are not allowed to visit ';
                $message_string .= !empty( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : 'the previous page';
                $this->add_message( $message_string, 'warning' );
                break;
            case 'finished_day':
                $this->add_message( 'Day finished successfully.', 'success' );
                break;
            case 'add_entry':
                if ( $message[ 'status' ] == 'success' ) {
                    $message_string = 'Successfully';
                    $message_type = 'success';
                    if ( $message[ 'action' ] == 'update' )
                        $message_string .= ' updated';
                    if ( $message[ 'action' ] == 'add' )
                        $message_string .= ' added';
                } else {
                    $message_string = 'Failed to';
                    $message_type = 'danger';
                    if ( $message[ 'action' ] == 'update' )
                        $message_string .= ' update the';
                    if ( $message[ 'action' ] == 'add' )
                        $message_string .= ' add a new ';
                }
                //print_r( $message[ 'data' ] );
                //print_r( $message[ 'columns' ] );

                if ( $message[ 'table' ] != 'users' )
                    $message_string .= ' ' . rtrim( $message[ 'table' ], 's' );//' customer';
                else
                    $message_string .= ' worker';
                if ( !empty( $message[ 'columns' ] ) && !empty( $message[ 'data' ] ) ) {

                    foreach ( $message[ 'columns' ] as $key => $column ) {
                        $data[ $column ] = $message[ 'data' ][ $key ];
                    }

                    switch ( $message[ 'table' ] ) {
                        case 'customers':
                        case 'furniture':
                            if ( !empty( $data[ 'name' ] ) )
                                $message_string .= ' named ' . $data[ 'name' ];
                            if ( !empty( $data[ 'ID' ] ) )
                                $message_string .= ' with ID of ' . $data[ 'ID' ];
                            break;
                        case 'jobs':
                        case 'shifts':
                            if ( !empty( $data[ 'ID' ] ) )
                                $message_string .= ' with ID of ' . $data[ 'ID' ];
                            break;
                        case 'users':
                            if ( !empty( $data[ 'name' ] ) )
                                $message_string .= ' named ' . $data[ 'name' ];
                            if ( !empty( $data[ 'pin' ] ) )
                                $message_string .= ' with pin number ' . $data[ 'pin' ];
                            break;
                    }
                }
                $message_string .= '.';
                $this->add_message( $message_string, $message_type );
                break;
        }
    }
}

/**
 * Main instance of ph_Messages.
 *
 * Returns the main instance of WC to prevent the need to use globals.
 *
 */
function ph_messages( $ph_user = false ) {
    return ph_Messages::instance( $ph_user );
}