<?php

namespace Phoenix;

use Phoenix\Utility\HTMLTags;

/**
 * Class Messages
 *
 * @package Phoenix
 */
class Messages extends Base
{
    /**
     * @var null|Messages
     */
    // protected static ?Messages $_instance = null;

    /**
     * @var array
     */
    public array $messages = [];

    /**
     * @var array
     */
    private array $emailArgs = [];

    /**
     * @var HTMLTags
     */
    private HTMLTags $htmlUtility;

    /**
     * @var bool
     */
    private bool $doingCRON = false;

    /**
     * @return Messages
     */
    /*
    public static function instance(): Messages
    {
        if ( self::$_instance === null ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    */

    public function doingCRON(): self
    {
        $this->doingCRON = true;
        return $this;
    }

    /**
     * Messages constructor.
     *
     * @param HTMLTags|null $htmlUtility
     */
    public function __construct(HTMLTags $htmlUtility = null)
    {
        if ( $htmlUtility !== null ) {
            $this->setHTMLUtility( $htmlUtility );
        }
    }

    /**
     * @param HTMLTags $htmlUtility
     * @return $this
     */
    public function setHTMLUtility(HTMLTags $htmlUtility): self
    {
        $this->htmlUtility = $htmlUtility;
        return $this;
    }

    /**
     * @return $this
     */
    public function initStatefulMessages(): self
    {
        if ( $this->doingCRON ) {
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
        if ( empty( $messageText ) ) {
            return false;
        }

        foreach ( $this->messages as $message ) {
            if ( $message['string'] === $messageText ) {
                return false;
            }
        }

        if ( $this->doingCRON ) {
            trigger_error( strip_tags( $this->emailArgs['prepend'] . $messageText ) );
            echo strip_tags( $messageText . "\r\n" );
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
     * @return string
     */
    public function getMessagesHTML(): string
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
        if ( !empty( $collapsedMessages ) ) { ?>
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
        <?php }
        unset( $_SESSION['messages'] );
        $_SESSION['messages'] = [];
        return ob_get_clean();
    }

    /**
     * @param array $emailArgs
     * @return $this
     */
    public function setEmailArgs(array $emailArgs = []): self
    {
        foreach ( $emailArgs as $key => $emailArg ) {
            $this->emailArgs[$key] = $emailArg;
        }
        return $this;
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
        $headers = 'From: ' . $emailArgs['from_name'] . ' CRM <' . $emailArgs['from'] . '>' . "\r\n"
            . 'Mime-Version: 1.0' . "\r\n"
            . 'Content-type: text/html; charset=UTF-8' . "\r\n";
        if ( is_string( $emailArgs['to'] ) ) {
            $emailArgs['to'] = [$emailArgs['to']];
        }
        foreach ( $emailArgs['to'] as $to ) {
            if ( !mail( $to, $emailArgs['subject'], '<h1>Results</h1>' . $emailContent, $headers ) ) {
                $this->add( 'Failed to email messages.' );
                return false;
            }
        }

        return true;
    }
}