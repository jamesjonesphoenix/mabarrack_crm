<?php

namespace Phoenix;

/**
 * Class Messages
 */
class Messages
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
     * @var array
     */
    public $message_codes = array();

    /**
     * @var User
     */
    public $current_user;

    /**
     * @return Messages|null
     */
    public static function instance(): ?Messages
    {
        if (self::$_instance === null) {
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
        $this->init($user);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function init(User $user = null): bool
    {
        $this->current_user = $user;

        //d($_SESSION['messages']);

        if (!empty($_SESSION['message'])) {
            $this->addStatefulMessages($_SESSION['message']);
            unset($_SESSION['message']);
        }
        if (!empty($_GET['message'])) {
            $this->addStatefulMessages($_GET['message']);
        }
        return true;
    }

    /**
     * @param string $message_string
     * @param string $message_type
     * @return bool
     */
    public function add(string $message_string = '', $message_type = 'danger'): bool
    {
        if (empty($message_string)) {
            return false;
        }
        $this->messages[] = array('string' => $message_string, 'type' => $message_type);
        return true;
    }

    /**
     * @return bool
     */
    public function isMessage(): bool
    {
        if (empty($this->messages)) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function display(): bool
    {
        if (count($this->messages) === 0) {
            return false;
        }

        $message_html = '<div class="messages">';
        foreach ($this->messages as $message) {
            if (!empty($message['string'])) {
                $message_type = !empty($message['type']) ? $message['type'] : 'danger';
                $message_html .= sprintf('<div class="alert alert-' . $message_type . '" role="alert">%s</div>', $message['string']);
            }
        }
        $message_html .= '</div>';
        echo $message_html;
        return true;
    }

    /**
     *
     */
    public function addSessionMessages()
    {
        if (empty($_SESSION['messages']) || !is_array($_SESSION['messages'])) {
            return false;
        }
        foreach ($_SESSION['messages'] as $message) {
            $this->formulate($message);
        }
        return true;
    }

    /**
     * @param array|string $messageInput
     * @return bool
     */
    public function addStatefulMessages($messageInput): bool
    {
        if (empty($messageInput)) {
            return false;
        }
        if (is_string($messageInput)) {
            $messages[] = array('code' => $messageInput);
        } elseif (!empty($messageInput['code'])) {
            $messages[] = $messageInput;
        } else {
            $messages = $messageInput;
        }
        foreach ($messages as $message) {
            if (is_string($message['code'])) {
                $this->formulate($message);
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
        if (empty($message)) {
            return false;
        }
        switch($message['code']) {
            case 'denied':
                if ($this->current_user) {
                    $message_string = 'You were redirected to the ' . $this->current_user->getRole() . ' homepage because you are not allowed to visit ';
                } else {
                    $message_string = 'You were redirected to this page because you are not allowed to visit ';
                }
                $message_string .= !empty($_GET['page']) ? $_GET['page'] : 'the previous page';
                $this->add($message_string, 'warning');
                break;
            case 'finished_day':
                $this->add('Day finished successfully.', 'success');
                break;
            case 'loggedIn':
                $this->add('Logged in successfully.', 'success');
                break;
            case 'add_entry':
                if ($message['status'] === 'success') {
                    $message_string = 'Successfully';
                    $message_type = 'success';
                    if ($message['action'] === 'update') {
                        $message_string .= ' updated';
                    }
                    if ($message['action'] === 'add') {
                        $message_string .= ' added';
                    }
                } else {
                    $message_string = 'Failed to';
                    $message_type = 'danger';
                    if ($message['action'] === 'update') {
                        $message_string .= ' update the';
                    }
                    if ($message['action'] === 'add') {
                        $message_string .= ' add a new ';
                    }
                }
                //print_r( $message[ 'data' ] );
                //print_r( $message[ 'columns' ] );

                if ($message['table'] !== 'users') {
                    $message_string .= ' ' . rtrim($message['table'], 's');
                }//' customer';
                else {
                    $message_string .= ' worker';
                }
                if (!empty($message['columns']) && !empty($message['data'])) {

                    foreach ($message['columns'] as $key => $column) {
                        $data[$column] = $message['data'][$key];
                    }

                    switch($message['table']) {
                        case 'customers':
                        case 'furniture':
                            if (!empty($data['name'])) {
                                $message_string .= ' named ' . $data['name'];
                            }
                            if (!empty($data['ID'])) {
                                $message_string .= ' with ID of ' . $data['ID'];
                            }
                            break;
                        case 'jobs':
                        case 'shifts':
                            if (!empty($data['ID'])) {
                                $message_string .= ' with ID of ' . $data['ID'];
                            }
                            break;
                        case 'users':
                            if (!empty($data['name'])) {
                                $message_string .= ' named ' . $data['name'];
                            }
                            if (!empty($data['pin'])) {
                                $message_string .= ' with pin number ' . $data['pin'];
                            }
                            break;
                    }
                }
                $message_string .= '.';
                $this->add($message_string, $message_type);
                break;
        }
        return true;
    }
}