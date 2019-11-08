<?php

namespace Phoenix;

/**
 * Class CronLogging
 */
class CronLogging
{
    /**
     * @var string
     */
    public $email_message = '';

    /**
     * @var string
     */
    public $messagePrepend = '';

    /**
     * @var array
     */
    public $emailArgs = array();

    /**
     * ph_Cron_Logging constructor.
     *
     * @param bool $messagePrepend
     * @param bool $emailArgs
     */
    public function __construct($messagePrepend = false, $emailArgs = false)
    {
        $this->init($messagePrepend, $emailArgs);
    }

    /**
     * @param bool $messagePrepend
     * @param bool $emailArgs
     * @return bool
     */
    public function init($messagePrepend = false, $emailArgs = false): bool
    {
        if (!empty($messagePrepend)) {
            $this->messagePrepend = $messagePrepend;
        }
        if (!empty($emailArgs)) {
            $this->emailArgs['subject'] = !empty($emailArgs['subject']) ? $emailArgs['subject'] : 'CRON log';

            if (!empty($emailArgs['to'])) {
                $this->emailArgs['to'] = $emailArgs['to'];
            } elseif (defined('TO_EMAIL')) {
                $this->emailArgs['to'] = TO_EMAIL;
            }

            if (!empty($emailArgs['from'])) {
                $this->emailArgs['from'] = $emailArgs['from'];
            } elseif (defined('FROM_EMAIL')) {
                $this->emailArgs['from'] = FROM_EMAIL;
            }
        }
        return true;
    }

    /**
     * @param string $message
     */
    public function add_log($message = 'false'): void
    {
        if (!empty($message)) {
            $message = $this->messagePrepend . $message;
            trigger_error($message);
            echo $message . "\r\n";
            $this->email_message .= $message . '<br>';
        }
    }

    /**
     * @return bool
     */
    public function email_log(): bool
    {
        if (empty($this->emailArgs['from']) || empty($this->emailArgs['to']) || empty($this->emailArgs['subject'])) {
            return false;
        }
        $headers = 'From: Mabarrack CRM <' . $this->emailArgs['from'] . '>' . "\r\n";
        $headers .= 'Mime-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        if (mail($this->emailArgs['to'], $this->emailArgs['subject'], '<h1>Results</h1>' . $this->email_message, $headers)) {
            return true;
        }
        return false;
    }
}