<?php

namespace Phoenix;

use Phoenix\Utility\HTMLTags;

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
     * @var HTMLTags
     */
    private HTMLTags $htmlUtility;


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
     * @param HTMLTags $htmlUtility
     * @return $this
     */
    public function init(HTMLTags $htmlUtility): self
    {
        $this->htmlUtility = $htmlUtility;
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
                            echo $this->htmlUtility::getAlertHTML( $message['string'], $message['type'] ?? 'danger' );
                        }
                    }
                    ?>
                    <?php if ( !empty( $collapsedMessages ) ) {
                        ?>
                        <div class="collapse-messages-column">
                            <?php
                            echo $this->htmlUtility::getAlertHTML(
                                '<span class="mr-2"><strong>' . ($numberOfMessages - $messagesDisplayLimit) . '</strong> additional messages not shown.</span><button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapse-messages" aria-expanded="false"
                            aria-controls="collapse-messages">Expand to display</button>'
                                , 'primary' );
                            ?>
                        </div>
                        <div class="collapse" id="collapse-messages">
                            <?php foreach ( $collapsedMessages as $key => $message ) {
                                echo $this->htmlUtility::getAlertHTML( $message['string'], $message['type'] ?? 'danger' );
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