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
     * @var null|Messages
     */
    protected static ?Messages $_instance = null;

    /**
     * @var array
     */
    public array $messages = [];

    /**
     * @var array
     */
    protected array $_emailArgs;


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
     */
    private function __construct()
    {
        //$this->init();
    }

    /**
     * @return $this
     */
    public function init(): self
    {
        if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
            return $this;
        }
        if ( !empty( $_SESSION['messages'] ) ) {
            $this->addStatefulMessages( $_SESSION['messages'] );
        }
        if ( !empty( $_GET['messages'] ) ) {
            $this->addStatefulMessages( $_GET['messages'] );
        }
        return $this;
    }

    /**
     * Adds a message to the queue
     *
     * @param string $messageText
     * @param string $messageType
     * @return bool
     */
    public function add(string $messageText = '', string $messageType = 'danger'): bool
    {
        //d( $messageText );
        if ( empty( $messageText ) ) {
            return false;
        }

        foreach ( $this->messages as $message ) {
            if ( $message['string'] === $messageText ) {
                return false;
            }
        }

        if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
            trigger_error( $this->emailArgs['prepend'] . $messageText );
            echo $messageText . "\r\n";
        }

        $message = ['string' => $messageText, 'type' => $messageType];

        $this->messages[] = $message;
        $_SESSION['messages'][] = $message;
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
     * @param array $messages
     * @return bool
     */
    public function addStatefulMessages(array $messages = []): bool
    {
        if ( empty( $messages ) ) {
            return false;
        }


        foreach ( $messages as $message ) {
            if ( empty( $message['string'] ) ) {
                continue;
            }
            $messageType = $message['type'] ?? '';
            $this->add( $message['string'], $messageType );

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
     * @param bool $showTitle
     * @return string
     */
    public function getMessagesHTML(bool $showTitle = true): string
    {
        $numberOfMessages = count( $this->messages );
        if ( $numberOfMessages === 0 ) {
            return false;
        }
        /*
         * primary
         * secondary
         * success
         * danger
         * warning
         * info
         * light
         * dark
         */
        $messages = $this->messages;
        end( $messages );
        $lastKey = key( $messages );

        $messagesDisplayLimit = 3;
        ob_start();
        ?>
        <div class="row">
            <div class="col">
                <?php
                if ( $showTitle ) { ?>
                    <div class="px-3">
                        <h2><i class="fas fa-sticky-note"></i> Messages</h2>
                    </div>
                <?php } ?>
                <div class="grey-bg px-3 py-2">
                    <?php
                    $i = 0;
                    foreach ( $messages as $key => $message ) {
                        if ( empty( $message['string'] ) ) {
                            continue;
                        }
                        $i++;
                        if ( $i > $messagesDisplayLimit ) {
                            $collapsedMessages[$key] = $message;
                        } else {
                            echo $this->getMessageHTML( $message['string'], $message['type'] ?? 'danger' );
                        }
                    }
                    ?>
                    <?php if ( !empty( $collapsedMessages ) ) {
                        ?>
                        <div class="collapse-messages-column">
                            <?php
                            echo $this->getMessageHTML(
                                '<span class="mr-2"><strong>' . ($numberOfMessages - $messagesDisplayLimit) . '</strong> additional messages not shown.</span><button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapse-messages" aria-expanded="false"
                            aria-controls="collapse-messages">Expand to display</button>'
                                , 'primary' );
                            ?>
                        </div>
                        <div class="collapse" id="collapse-messages">
                            <?php foreach ( $collapsedMessages as $key => $message ) {
                                echo $this->getMessageHTML( $message['string'], $message['type'] ?? 'danger' );
                            } ?>
                        </div>
                        <?php
                    } ?>
                </div>
            </div>
        </div>
        <?php
        unset( $_SESSION['messages'] );
        $_SESSION['messages'] = [];
        return ob_get_clean();
    }

    /**
     * @param string $string
     * @param string $type
     * @param bool   $showCloseButton
     * @return string
     */
    public function getMessageHTML(string $string = '', string $type = '', bool $showCloseButton = true): string
    {
        $type ??= 'danger';
        ob_start();
        ?>
        <div class="alert alert-<?php echo $type; ?>  my-2" role="alert">
            <?php if ( $showCloseButton ) { ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            <?php } ?>
            <?php echo $string; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param array $emailArgs
     * @return array
     */
    protected
    function emailArgs(array $emailArgs = []): array
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
     * @return bool
     */
    public
    function email(): bool
    {
        $emailArgs = $this->emailArgs;
        if ( empty( $emailArgs['from'] ) || empty( $emailArgs['to'] ) || empty( $emailArgs['subject'] ) ) {
            $this->add( "Can't email messages. Email args missing." );
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