<?php

namespace Phoenix;

/**
 * @property array $emailArgs
 *
 * Class Messages
 *
 * @package Phoenix
 */
class Messages extends Base
{
    /**
     * @var null
     */
    protected static $_instance;

    /**
     * @var array
     */
    public $messages = array();

    /**
     * @var User
     */
    public $currentUser;

    /**
     * @var array
     */
    protected $_emailArgs;


    /**
     * @return Messages
     */
    public static function instance(): Messages
    {
        if ( self::$_instance === null ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Messages constructor.
     *
     * @param User $user
     */
    private function __construct(User $user = null)
    {
        $this->currentUser = $user;
        $this->init();
    }

    /**
     * @return bool
     */
    public function init(): bool
    {
        if ( !defined( 'DOING_CRON' ) || !DOING_CRON ) {
            if ( !empty( $_SESSION['message'] ) ) {
                $this->addStatefulMessages( $_SESSION['message'] );
                unset( $_SESSION['message'] );
            }
            if ( !empty( $_GET['message'] ) ) {
                $this->addStatefulMessages( $_GET['message'] );
            }
        }
        return true;
    }

    /**
     * @param array $emailArgs
     * @return array
     */
    protected function emailArgs(array $emailArgs = []): array
    {
        if ( !empty( $this->_emailArgs ) ) {
            return $this->_emailArgs;
        }

        $this->_emailArgs['prepend'] = $emailArgs['prepend'] ?? '';
        $this->_emailArgs['subject'] = $emailArgs['subject'] ?? 'CRON log';
        $this->_emailArgs['to'] = $emailArgs['to'] ?? TO_EMAIL ?? '';
        $this->_emailArgs['from'] = $emailArgs['from'] ?? FROM_EMAIL ?? '';
        return $this->_emailArgs;
    }

    /**
     * @param string $message
     * @param string $messageType
     * @return bool
     */
    public function add(string $message = '', $messageType = 'danger'): bool
    {
        if ( empty( $message ) ) {
            return false;
        }
        if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
            trigger_error( $this->emailArgs['prepend'] . $message );
            echo $message . "\r\n";
        }

        $this->messages[] = array('string' => $message, 'type' => $messageType);
        return true;
    }


    /**
     * @return bool
     */
    public function isMessage(): bool
    {
        if ( empty( $this->messages ) ) {
            return false;
        }
        return true;
    }


    /**
     *
     */
    public function addSessionMessages()
    {
        if ( empty( $_SESSION['messages'] ) || !is_array( $_SESSION['messages'] ) ) {
            return false;
        }
        foreach ( $_SESSION['messages'] as $message ) {
            $this->formulate( $message );
        }
        return true;
    }

    /**
     * @param array|string $messageInput
     * @return bool
     */
    public function addStatefulMessages($messageInput): bool
    {
        if ( empty( $messageInput ) ) {
            return false;
        }
        if ( is_string( $messageInput ) ) {
            $messages[] = array('code' => $messageInput);
        } elseif ( !empty( $messageInput['code'] ) ) {
            $messages[] = $messageInput;
        } else {
            $messages = $messageInput;
        }
        foreach ( $messages as $message ) {
            if ( is_string( $message['code'] ) ) {
                $this->formulate( $message );
            }
        }
        return true;
    }

    /**
     * @param bool $message
     * @return bool
     */
    public function formulate($message = false): bool
    {
        if ( empty( $message ) ) {
            return false;
        }
        switch( $message['code'] ) {
            case 'denied':
                if ( $this->currentUser ) {
                    $messageString = 'You were redirected to the ' . $this->currentUser->role . ' homepage because you are not allowed to visit ';
                } else {
                    $messageString = 'You were redirected to this page because you are not allowed to visit ';
                }
                $messageString .= !empty( $_GET['page'] ) ? $_GET['page'] : 'the previous page';
                $this->add( $messageString, 'warning' );
                break;
            case 'finished_day':
                $this->add( 'Day finished successfully.', 'success' );
                break;
            case 'loggedIn':
                $this->add( 'Logged in successfully.', 'success' );
                break;
            case 'add_entry':
                if ( $message['status'] === 'success' ) {
                    $messageString = 'Successfully';
                    $messageType = 'success';
                    if ( $message['action'] === 'update' ) {
                        $messageString .= ' updated';
                    }
                    if ( $message['action'] === 'add' ) {
                        $messageString .= ' added';
                    }
                } else {
                    $messageString = 'Failed to';
                    $messageType = 'danger';
                    if ( $message['action'] === 'update' ) {
                        $messageString .= ' update the';
                    }
                    if ( $message['action'] === 'add' ) {
                        $messageString .= ' add a new ';
                    }
                }

                if ( $message['table'] !== 'users' ) {
                    $messageString .= ' ' . rtrim( $message['table'], 's' );
                }//' customer';
                else {
                    $messageString .= ' worker';
                }
                if ( !empty( $message['data'] ) ) {

                    switch( $message['table'] ) {
                        case 'customers':
                        case 'furniture':
                            if ( !empty( $data['name'] ) ) {
                                $messageString .= ' named ' . $data['name'];
                            }
                            if ( !empty( $data['ID'] ) ) {
                                $messageString .= ' with ID of ' . $data['ID'];
                            }
                            break;
                        case 'jobs':
                        case 'shifts':
                            if ( !empty( $data['ID'] ) ) {
                                $messageString .= ' with ID of ' . $data['ID'];
                            }
                            break;
                        case 'users':
                            if ( !empty( $data['name'] ) ) {
                                $messageString .= ' named ' . $data['name'];
                            }
                            if ( !empty( $data['pin'] ) ) {
                                $messageString .= ' with pin number ' . $data['pin'];
                            }
                            break;
                    }
                }
                $messageString .= '.';
                $this->add( $messageString, $messageType );
                break;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function display(): bool
    {
        if ( count( $this->messages ) === 0 ) {
            return false;
        }

        $messageHTML = '<div class="container messages mt-5">';
        foreach ( $this->messages as $message ) {
            if ( !empty( $message['string'] ) ) {
                $messageType = !empty( $message['type'] ) ? $message['type'] : 'danger';
                $messageHTML .= sprintf( '<div class="row"><div class="col"><div class="alert alert-' . $messageType . '" role="alert">%s</div></div></div>', $message['string'] );
            }
        }
        $messageHTML .= '</div>';
        echo $messageHTML;
        return true;
    }

    /**
     * @return bool
     */
    public function email(): bool
    {
        $emailArgs = $this->emailArgs;
        if ( empty( $emailArgs['from'] ) || empty( $emailArgs['to'] ) || empty( $emailArgs['subject'] ) ) {
            $this->add( 'Can\'t email messages. Email args missing' );
            return false;
        }

        $emailContent = '';
        foreach ( $this->messages as $message ) {
            if ( !empty( $message['string'] ) ) {
                $emailContent .= $emailArgs['prepend'] . $message['string'] . '<br>';
            }
        }

        $headers = 'From: ' . SYSTEM_TITLE . ' CRM <' . $emailArgs['from'] . '>' . "\r\n";
        $headers .= 'Mime-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        if ( mail( $emailArgs['to'], $emailArgs['subject'], '<h1>Results</h1>' . $emailContent, $headers ) ) {
            return true;
        }

        //function mail ($to, $subject, $message, $additional_headers = null, $additional_parameters = null) {}

        $this->add( 'Failed to email messages.' );
        return false;
    }
}